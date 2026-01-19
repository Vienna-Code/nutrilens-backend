<?php

namespace App\Controller;

use App\Dto\Commerces;
use App\Dto\_NestedObjects\ContactInfo;
use App\Dto\_NestedObjects\CommerceSchedule;
use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Enum\ReportType;
use App\Enum\UserRank;
use App\Repository\CommerceReportRepository;
use App\Repository\CommerceRepository;
use App\Service\CommerceManager;
use App\Service\CommerceReportManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommerceController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private ValidationService $validation,
        private LoggerInterface $logger,

        private CommerceRepository $commerceRepository,
        private CommerceManager $commerceManager,

        private CommerceReportRepository $cReportRepository,
        private CommerceReportManager $cReportManager,

        private SerializerInterface $serializer,
    ) {}

    #[Route('/commerces/check-location', methods: ['GET'], name: 'app_commerce_check_location')]
    public function checkLocation(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'coords' => [
                        new Assert\NotBlank(),
                        new Assert\Regex(
                            pattern: '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/',
                            message: 'This value should follow the format "lat,lon"'
                        ),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar comercio
        [$lat, $lon] = explode(',', $data['coords']);
        $commerce = $this->commerceRepository->findOneBy([
            'coordsLat' => $lat,
            'coordsLon' => $lon
        ]);

        // Retornar información
        if ($commerce) {
            return $this->json([
                'error' => ['message' => 'Ya existe un comercio en estas coordenadas.']
            ], 409);
        } else {
            return $this->json([
                'message' => 'No existe un comercio en estas coordenadas.'
            ], 200);
        }
    }

    #[Route('/commerces/{id}', methods: ['GET'], name: 'app_commerce_get')]
    public function get(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\NotBlank(),
                        new Assert\Regex([
                            'pattern' => '/^\d+$/',
                            'message' => 'The id must be a positive integer.',
                        ]),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        $commerce = $this->commerceRepository->find($id);

        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }

    #[Route('/commerces', methods: ['GET'], name: 'app_commerce_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    // TODO
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar comercios
        $commerces = $this->commerceRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/commerces', methods: ['POST'], name: 'app_commerce_post')]
    public function post(Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        
        // Validación con DTO
        // TODO

        // Crear comercio, horarios & reportes
        $commerce = $this->commerceManager->create($data, $user);
        
        // Responder
        return $this->json([
            'message' => 'Comercio registrado correctamente.',
            'data' => $commerce,
        ], 201, [], ['groups' => ['commerce:create']]);
    }

    #[Route('/commerces/{id}', methods: ['PATCH'], name: 'app_commerce_patch')]
    public function patch(Commerce $commerce, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validacion con DTO
        // TODO

        // Modificar comercio
        $commerce = $this->commerceManager->update($data, $commerce, $user);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'No tiene la autoridad para actualizar este comercio.']
            ], 403);
        }

        // Responder
        return $this->json([
            'message' => 'Comercio modificado.',
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:update']]);
    }

    #[Route('/commerces/{id}', methods: ['DELETE'], name: 'app_commerces_delete')]
    public function delete(Commerce $commerce): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Eliminar comercio
        $this->commerceManager->delete($commerce);

        return $this->json([
            'message' => 'Comercio eliminado.'
        ], 200);
    }

    #[Route('/commerces/{idc}/reports/{idr}', methods: ['GET'], name: 'app_commercereport_get')]
    public function getReport(int $idc, int $idr): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Encontrar reporte
        $report = $this->cReportRepository->find($idr);
        if (!$report) {
            return $this->json([
                'error' => ['message' => 'Reporte no encontrado.']
            ], 404);
        }

        // Responder
        return $this->json([
            'data' => $report
        ], 200, [], ['groups' => ['commercereport:read']]);
    }

    #[Route('/commerces/{id}/reports', methods: ['GET'], name: 'app_commercereport_list')]
    public function listReports(int $id, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        // TODO

        // Encontrar reportes
        $commerce = $this->commerceRepository->find($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        $reports = $this->cReportRepository->findByFilters($data, $commerce);

        // Responder
        return $this->json([
            'data' => $reports
        ], 200, [], ['groups' => ['commercereport:list']]);
    }

    #[Route('/commerces/{id}/reports', methods: ['POST'], name: 'app_commercereport_create')]
    public function createReport(int $id, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        $type = ReportType::tryFrom($data['type']);

        // Encontrar comercio
        $commerce = $this->commerceRepository->findWithReports($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Verificación extra
        // No reportar comercios que subiste
        if ($commerce->getCommerceReports()->exists(
            fn ($key, $report) => $report->getUser() === $user && $report->getType() === ReportType::SUBMISSION
        )) {
            return $this->json([
                'error' => ['message' => 'No puedes reportar comercios que hayas subido.']
            ], 403);
        }
        // Chequeo de existencia
        if ($commerce->getCommerceReports()->exists(
            fn ($key, $report) =>
                $report->getUser() === $user &&
                $report->getType() === $type &&
                $report->getType() !== ReportType::ISSUE
        )) {
            return $this->json([
                'error' => ['message' => 'Ya subiste este reporte.']
            ], 409);
        }
        
        // Validación con DTO
        // TODO
        if (!\in_array($type, [
            ReportType::CONFIRMATION,
            ReportType::REBUTTAL,
            ReportType::ISSUE
        ])) {
            return $this->json([
                'error' => ['message' => 'Tipo de reporte inválido.']
            ], 400);
        }

        // Crear reporte
        $report = $this->cReportManager->create($data, $commerce, $user);
        
        // Responder
        return $this->json([
            'message' => 'Reporte de comercio creado correctamente.',
            'data' => $report,
        ], 201, [], ['groups' => ['commercereport:create']]);
    }

    #[Route('/commerces/{idc}/reports/{idr}', methods: ['PATCH'], name: 'app_commercereport_patch')]
    public function patchReport(int $idc, int $idr, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación con DTO
        // TODO

        // Actualizar reporte
        $report = $this->cReportRepository->find($idr);
        if (!$report) {
            return $this->json([
                'error' => ['message' => 'Reporte no encontrado.']
            ], 404);
        }
        $report = $this->cReportManager->update($data, $report);

        // Responder
        return $this->json([
            'message' => 'Reporte de comercio actualizado correctamente.',
            'data' => $report,
        ], 200, [], ['groups' => ['commercereport:update']]);
    }
}
