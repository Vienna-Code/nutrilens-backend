<?php

namespace App\Controller;

use App\Repository\CommerceReportRepository;
use App\Repository\ProductReportRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReportController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,

        private CommerceReportRepository $cReportRepository,
        private ProductReportRepository $pReportRepository,
    ) {}

    #[Route('/reports', name: 'app_reports_list')]
    public function list(Request $request): JsonResponse
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

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'resource' => new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Choice(['commerces', 'products']),
                    ]),
                    'resolved' => new Assert\Optional([
                        new Assert\Type('string'),
                        new Assert\Choice(['true', 'null', 'false']),
                    ]),
                    'user' => new Assert\Optional([
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ]),
                    'orderBy' => new Assert\Optional([
                        new Assert\Type('string'),
                        new Assert\Choice([
                            'date_asc', 'date_desc',
                        ]),
                    ]),
                    'page' => new Assert\Optional([
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ]),
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar y responder
        if ($data['resource'] === 'commerces') {
            $reports = $this->cReportRepository->findByFilters($data, null);
            return $this->json([
                'data' => $reports
            ], 200, [], ['groups' => ['commercereport:list']]);
        } else {
            $reports = $this->pReportRepository->findByFilters($data, null);
            return $this->json([
                'data' => $reports
            ], 200, [], ['groups' => ['productreport:list']]);
        }
    }
}
