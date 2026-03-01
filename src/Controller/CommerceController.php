<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Enum\AlimentaryRestriction;
use App\Enum\CommerceType;
use App\Enum\PaymentMethod;
use App\Enum\ReportType;
use App\Repository\CommerceReportRepository;
use App\Repository\CommerceRepository;
use App\Service\CommerceManager;
use App\Service\CommerceReportManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommerceController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private CommerceRepository $commerceRepository,
        private CommerceManager $commerceManager,

        private CommerceReportRepository $cReportRepository,
        private CommerceReportManager $cReportManager,

        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
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
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Obtener comercio
        $commerce = $this->commerceRepository->find($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        $commerce = $this->normalizer->normalize($commerce, context: [
            'groups' => ['commerce:read']
        ]);
        
        // Agregar si fue reportado como verificado o no
        if ($user) {
            $reports = $this->cReportRepository->findBy([
                'commerce' => $commerce,
                'user' => $user,
            ]);
            foreach ($reports as $report) {
                $vote = match ($report->getType()) {
                    ReportType::CONFIRMATION => true,
                    ReportType::REBUTTAL     => false,
                    default                  => $vote ?? null,
                };
            }
            $commerce['userVerificationReport'] = $vote ?? null;
        }

        // Responder
        return $this->json([
            'data' => $commerce
        ], 200);
    }

    #[Route('/commerces', methods: ['GET'], name: 'app_commerce_list')]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Obtener parametros URL
        $data = $request->query->all();

        // Validación
        if (isset($data['restrictions'])) {
            $data['restrictions'] = array_filter(array_map('trim', explode(',', $data['restrictions'])));
        }
        if (isset($data['commerceTypes'])) {
            $data['commerceTypes'] = array_filter(array_map('trim', explode(',', $data['commerceTypes'])));
        }
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'lat' => [
                        new Assert\Regex(
                            pattern: '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/',
                            message: 'This value should follow the format "float,float"'
                        ),
                    ],
                    'lon' => [
                        new Assert\Regex(
                            pattern: '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/',
                            message: 'This value should follow the format "float,float"'
                        ),
                    ],
                    'name' => [],
                    'minPrice' => [
                        new Assert\Type(['type' => 'numeric']),
                        new Assert\Positive(),
                    ],
                    'maxPrice' => [
                        new Assert\Type(['type' => 'numeric']),
                        new Assert\Positive(),
                    ],
                    'restrictions' => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Choice(array_column(AlimentaryRestriction::cases(), 'value')),
                        ]),
                    ],
                    'orderBy' => [
                        new Assert\Choice([
                            'name_asc', 'name_desc',
                            'rating_asc', 'rating_desc',
                            'price_asc', 'price_desc',
                        ]),
                    ],
                    'commerceTypes' => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Choice(array_column(CommerceType::cases(), 'value')),
                        ]),
                    ],
                    'unverified' => [],
                ],
                'allowMissingFields' => true,
            ])
        );

        // Encontrar comercios
        $commerces = $this->commerceRepository->findByFilters($data);

        // Agregar si fue reportado como verificado o no
        foreach ($commerces as &$commerce) {
            $commerce = $this->normalizer->normalize($commerce, context: [
                'groups' => ['commerce:list']
            ]);

            if ($user) {
                $reports = $this->cReportRepository->findBy([
                    'commerce' => $commerce,
                    'user' => $user,
                ]);
                foreach ($reports as $report) {
                    $vote = match ($report->getType()) {
                        ReportType::CONFIRMATION => true,
                        ReportType::REBUTTAL     => false,
                        default                  => $vote ?? null,
                    };
                }
                $commerce['userVerificationReport'] = $vote ?? null;
            }
        }

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200);
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
        
        // Validación
        $data = $this->validateCommerce($data);

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

        // Validacion
        $data = $this->validateCommerce($data, true);

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
    public function delete(string $id): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Obtener comercio
        $commerce = $this->commerceRepository->find($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Eliminar comercio
        $this->commerceManager->delete($commerce);

        return $this->json([
            'message' => 'Comercio eliminado.'
        ], 200);
    }

    #[Route('/commerces/{idc}/reports/{idr}', methods: ['GET'], name: 'app_commercereport_get')]
    public function getReport(string $idc, string $idr): JsonResponse {
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

        // Validación
        $this->validate(
            ['idc' => $idc, 'idr' => $idr],
            new Assert\Collection([
                'fields' => [
                    'idc' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'idr' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar reporte
        $report = $this->cReportRepository->findOneBy([
            'id' => $idr,
            'commerce' => $idc,
        ]);
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
    public function listReports(string $id, Request $request): JsonResponse
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
        $data['commerceId'] = $id;

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'commerceId' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'resolved' => [
                        new Assert\Type('string'),
                        new Assert\Choice(['true', 'null', 'false']),
                    ]
                ],
                'allowExtraFields' => true,
                'allowMissingFields' => true,
            ])
        );

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
    public function createReport(string $id, Request $request): JsonResponse
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
        $data['commerceId'] = $id;
        
        // Validación
        $data = $this->validateCommerceReport($data);
        $type = ReportType::tryFrom($data['type']);

        // Encontrar comercio
        $commerce = $this->commerceRepository->findWithReports($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Verificación extra
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

        // Crear reporte
        $report = $this->cReportManager->create($data, $commerce, $user);
        
        // Responder
        return $this->json([
            'message' => 'Reporte de comercio creado correctamente.',
            'data' => $report,
        ], 201, [], ['groups' => ['commercereport:create']]);
    }

    #[Route('/commerces/{idc}/reports/{idr}', methods: ['PATCH'], name: 'app_commercereport_patch')]
    public function patchReport(string $idc, string $idr, Request $request): JsonResponse
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

        // Validación
        $data = $this->validateCommerceReport($data, true);

        // Actualizar reporte
        $report = $this->cReportRepository->findOneBy([
            'id' => $idr,
            'commerce' => $idc,
        ]);
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

    private function validateCommerce(array $input, bool $patch = false): array
    {
        $fields = [
            'name' => [
                new Assert\Type('string'),
                new Assert\Length(max: 50),
            ],
            'type' => [
                new Assert\Choice(array_column(CommerceType::cases(), 'value')),
            ],
            'coordsLat' => [
                new Assert\Type(['type' => 'numeric']),
                new Assert\Range(min: -90, max: 90),
            ],
            'coordsLon' => [
                new Assert\Type(['type' => 'numeric']),
                new Assert\Range(min: -180, max: 180),
            ],
            'address' => [
                new Assert\Type('string'),
            ],
            'contactInfo' => [
                new Assert\Type('array'),
                new Assert\Unique(),
                new Assert\Collection([
                    'fields' => [
                        'number' => [
                            new Assert\Regex(
                                pattern: '/^\+?[0-9\s\-().]{6,20}$/',
                                message: 'Invalid phone number.'
                            ),
                        ],
                        'email' => [
                            new Assert\Email(message: 'Invalid email address.'),
                        ],
                    ],
                    'allowMissingFields' => true,
                    'allowExtraFields' => false,
                ]),
            ],
            'paymentMethods' => [
                new Assert\Type('array'),
                new Assert\Unique(),
                new Assert\All([
                    new Assert\Choice(array_column(PaymentMethod::cases(), 'value')),
                ]),
            ],
            'commerceSchedules' => [
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Collection([
                        'fields' => [
                            'weekday' => [
                                new Assert\NotNull(),
                                new Assert\Type('integer'),
                                new Assert\Range(
                                    min: 0,
                                    max: 6,
                                    notInRangeMessage: 'Weekday must be between 0 (Sunday) and 6 (Saturday)'
                                ),
                            ],
                            'opensAt' => [
                                new Assert\NotBlank(),
                                new Assert\DateTime(
                                    format: \DateTimeInterface::ATOM,
                                    message: 'opensAt must be a valid datetime'
                                ),
                            ],
                            'closesAt' => [
                                new Assert\NotBlank(),
                                new Assert\DateTime(
                                    format: \DateTimeInterface::ATOM,
                                    message: 'closesAt must be a valid datetime'
                                ),
                            ],
                        ],
                        'allowMissingFields' => false,
                        'allowExtraFields' => false,
                    ]),
                ]),
                new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                    if (!\is_array($value)) {
                        return;
                    }

                    $weekdays = [];
                    foreach ($value as $i => $schedule) {
                        // Chequeo de día repetido
                        if (isset($schedule['weekday'])) {
                            if (\in_array($schedule['weekday'], $weekdays, true)) {
                                $context->buildViolation('Duplicate weekday entry')
                                    ->atPath("[$i].weekday")
                                    ->addViolation();
                            }
                            $weekdays[] = $schedule['weekday'];
                        }

                        // Lógica de horarios
                        if (
                            isset($schedule['opensAt'], $schedule['closesAt'])
                            && \is_string($schedule['opensAt'])
                            && \is_string($schedule['closesAt'])
                        ) {
                            $opensAt = new \DateTimeImmutable($schedule['opensAt']);
                            $closesAt = new \DateTimeImmutable($schedule['closesAt']);

                            if ($opensAt >= $closesAt) {
                                $context->buildViolation('opensAt must be before closesAt')
                                    ->atPath("[$i]")
                                    ->addViolation();
                            }
                        }
                    }
                }),
            ],
            'images' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\Uuid(),
                ]),
            ]),
            'verified' => [
                new Assert\Type('bool'),
            ]
        ];

        if (!$patch) {
            unset($fields['verified']);
        }

        return $this->validate(
            $input,
            new Assert\Collection([
                'fields' => $fields,
                'allowMissingFields' => $patch,
            ])
        );
    }

    private function validateCommerceReport(array $input, bool $patch = false): array
    {
        $fields = [
            'commerceId' => [
                new Assert\Type('digit'),
                new Assert\Positive(),
            ],
            'type' => [
                new Assert\Type('string'),
                new Assert\Choice([
                    ReportType::CONFIRMATION->value,
                    ReportType::REBUTTAL->value,
                    ReportType::ISSUE->value,
                ]),
            ],
            'content' => [
                new Assert\Type('string'),
                new Assert\Length(max: 1000),
            ],
            'resolved' => [
                new Assert\AtLeastOneOf([
                    new Assert\Type('bool'),
                    new Assert\IsNull(),
                ]),
            ],
            'image' => new Assert\Optional([
                new Assert\Type('string'),
                new Assert\Uuid(),
            ]),
        ];

        if (!isset($input['type']) || $input['type'] !== ReportType::ISSUE->value) {
            unset($fields['content']);
        }

        if (!$patch) {
            unset($fields['resolved']);
        } else {
            unset($fields['type']);
            unset($fields['content']);
        }

        return $this->validate(
            $input,
            new Assert\Collection([
                'fields' => $fields,
                'allowMissingFields' => false,
            ])
        );
    }
}
