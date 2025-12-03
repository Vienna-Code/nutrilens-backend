<?php

namespace App\Controller;

use App\Dto\Commerces;
use App\Dto\_NestedObjects\ContactInfo;
use App\Dto\_NestedObjects\CommerceSchedule;
use App\Entity\Commerce;
use App\Enum\ReportType;
use App\Enum\UserRank;
use App\Repository\CommerceReportRepository;
use App\Repository\CommerceRepository;
use App\Service\CommerceManager;
use App\Service\CommerceReportManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class CommerceController extends AbstractController
{
    public function __construct(
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

        // Validación con DTO
        // TODO

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
    public function get(Commerce $commerce): JsonResponse
    {
        // Responder
        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }

    #[Route('/commerces', methods: ['GET'], name: 'app_commerce_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        $errors = $this->validation->validate(new Commerces\ListCommerces($data));
        if ($errors) return $errors;

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

    #[Route('/commerces/{id}/reports', methods: ['GET'], name: 'app_commercereport_list')]
    public function listReports(Commerce $commerce, Request $request): JsonResponse
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
        $reports = $this->cReportRepository->findByFilters($data, $commerce);

        // Responder
        return $this->json([
            'data' => $reports
        ], 200, [], ['groups' => ['commercereport:list']]);
    }

    #[Route('/commerces/{id}/reports', methods: ['POST'], name: 'app_commercereport_create')]
    public function createReport(Commerce $commerce, Request $request): JsonResponse
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
        if ($commerce->isVerified()) {
            $data['type'] = ReportType::ISSUE->value;
        } else if (!\in_array(ReportType::tryFrom($data['type']), [
            ReportType::CONFIRMATION,
            ReportType::REBUTTAL,
            ReportType::ISSUE
        ])) {
            return $this->json([
                'error' => ['message' => 'Tipo de reporte invalido.']
            ], 400);
        }

        // Crear comercio, horarios & reportes
        $report = $this->cReportManager->create($data, $commerce, $user);
        
        // Responder
        return $this->json([
            'message' => 'Reporte de comercio creado correctamente.',
            'data' => $report,
        ], 201, [], ['groups' => ['commercereport:create']]);
    }
}
