<?php

namespace App\Controller;

use App\Entity\Commerce;
use App\Entity\Review;
use App\Enum\Visibility;
use App\Repository\CommerceRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewVoteRepository;
use App\Service\ReviewManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReviewController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private NormalizerInterface $normalizer,

        private ReviewRepository $reviewRepository,
        private CommerceRepository $commerceRepository,
        private ReviewManager $rm,

        private ReviewVoteRepository $rvRepository,
    ) {}

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['GET'], name: 'app_review_get')]
    public function get(string $idc, string $idr): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Validación
        $this->validate(
            ['idr' => $idr, 'idc' => $idc],
        new Assert\Collection([
                'fields' => [
                    'idc' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'idr' => new Assert\AtLeastOneOf([
                        'constraints' => [
                            new Assert\Sequentially([
                                new Assert\Type('digit'),
                                new Assert\Positive(),
                            ]),
                            new Assert\IdenticalTo('me'),
                        ],
                    ]),
                ],
                'allowExtraFields' => true,
            ])
        );
        
        // Encontrar reseña
        $commerce = $this->commerceRepository->find((int)$idc);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        if ($idr === 'me') {
            if (!$user) {
                return $this->json([
                    'error' => ['message' => 'Autenticación requerida.']
                ], 401);
            }
            $review = $this->reviewRepository->findOneBy([
                'commerce' => $commerce,
                'user' => $user
            ]);
        } else {
            $review = $this->reviewRepository->findOneBy([
                'commerce' => $commerce,
                'id' => (int)$idr
            ]);
        }
        if (!$review) {
            return $this->json([
                'error' => ['message' => 'Reseña no encontrada.']
            ], 404);
        }

        $reviewData = $this->normalizer->normalize($review, context: [
            'groups' => ['review:read']
        ]);

        // Agregar si le diste like o no
        if ($user) {
            $vote = $this->rvRepository->findOneBy([
                'review' => $review,
                'user' => $user,
            ]);
            if ($vote) {
                $vote = $vote->isPositive();
            }
            $reviewData['liked'] = $vote;
        } else {
            $reviewData['liked'] = null;
        }

        // Responder
        return $this->json([
            'data' => $reviewData
        ], 200);
    }

    #[Route('/commerces/{idc}/reviews', methods: ['GET'], name: 'app_review_list')]
    public function list(string $idc, Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Validación
        $this->validate(
            ['idc' => $idc],
        new Assert\Collection([
                'fields' => [
                    'idc' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                ],
                'allowMissingFields' => true,
            ])
        );

        // Obtener parametros URL
        $data = $request->query->all();
        $data['commerce'] = (int)$idc;

        // Encontrar reviews
        $reviews = $this->reviewRepository->findByFilters($data);

        // Agregar si les diste like o no
        foreach ($reviews as &$review) {
            $review = $this->normalizer->normalize($review, context: [
                'groups' => ['review:list']
            ]);

            $vote = $this->rvRepository->findOneBy([
                'review' => $review,
                'user' => $user,
            ]);
            if ($vote) {
                $vote = $vote->isPositive();
            }
            $review['liked'] = $vote;
        }

        return $this->json([
            'data' => $reviews
        ], 200);
    }

    #[Route('/commerces/{idc}/reviews', methods: ['POST'], name: 'app_review_post')]
    public function post(string $idc, Request $request): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        $data['commerceId'] = $idc;

        // Validación
        $data = $this->validateReview($data);

        // Encontrar comercio
        $commerce = $this->commerceRepository->find($idc);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Crear review
        if ($this->reviewRepository->findOneBy(['user' => $user, 'commerce' => $commerce])) {
            return $this->json([
                'error' => ['message' => 'Ya hay una reseña creada para este comercio por este usuario.'],
            ], 403);
        }
        $review = $this->rm->create($data, $user, $commerce);

        return $this->json([
            'message' => 'Reseña creada correctamente.',
            'data' => $review,
        ], 201, [], ['groups' => ['review:create']]);
    }

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['PATCH'], name: 'app_review_patch')]
    public function patch(string $idc, string $idr, Request $request): JsonResponse {
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
        $data = $this->validateReview($data, true);

        // Encontrar reseña
        $commerce = $this->commerceRepository->find((int)$idc);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        $review = $this->reviewRepository->findOneBy([
            'commerce' => $commerce,
            'id' => (int)$idr
        ]);

        // Actualizar review
        $review = $this->rm->update($data, $review, $user);
        if (!$review) {
            return $this->json([
                'error' => ['message' => 'No tiene la autoridad para actualizar esta reseña.']
            ], 403);
        }

        return $this->json([
            'message' => 'Reseña actualizada correctamente.',
            'data' => $review,
        ], 200, [], ['groups' => ['review:update']]);
    }

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['DELETE'], name: 'app_review_delete')]
    public function delete(string $idc, string $idr): JsonResponse {
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
            ['idr' => $idr, 'idc' => $idc],
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
        
        // Encontrar reseña
        $commerce = $this->commerceRepository->find((int)$idc);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        $review = $this->reviewRepository->findOneBy([
            'commerce' => $commerce,
            'id' => (int)$idr
        ]);
        if (!$review) {
            return $this->json([
                'error' => ['message' => 'Reseña no encontrada.']
            ], 404);
        }

        // Eliminar reseña
        $this->rm->delete($review);

        return $this->json([
            'message' => 'Reseña eliminada.'
        ], 200);
    }

    #[Route('/commerces/{idc}/reviews/{idr}/vote', methods: ['PATCH'], name: 'app_review_vote_patch')]
    public function votePatch(string $idc, string $idr, Request $request): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        $data['idc'] = $idc;
        $data['idr'] = $idr;

        // Validación
        $this->validate(
            $data,
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
                    'positive' => [
                        new Assert\AtLeastOneOf([
                            new Assert\Type('bool'),
                            new Assert\IsNull(),
                        ]),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar reseña
        $commerce = $this->commerceRepository->find((int)$idc);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        $review = $this->reviewRepository->findOneBy([
            'commerce' => $commerce,
            'id' => (int)$idr
        ]);
        if (!$review) {
            return $this->json([
                'error' => ['message' => 'Reseña no encontrada.']
            ], 404);
        }
        if ($review->getUser() === $user) {
            return $this->json([
                'error' => ['message' => 'No puedes votar tu propia reseña']
            ], 400);
        }

        // Agregar voto
        if ($this->rm->vote($review, $user, $data['positive'])) {
            return $this->json([
                'message' => 'Se votó la reseña correctamente.'
            ], 200);
        } else {
            return $this->json([
                'message' => 'La reseña ya estaba votada de esta manera, no se realizaron cambios.'
            ], 200);
        }
    }

    private function validateReview(array $input, bool $patch = false): array {
        $fields = [
            'commerceId' => [
                new Assert\Type(['type' => 'digit']),
                new Assert\Positive(),
            ],
            'content' => [
                new Assert\Type('string'),
                new Assert\Length(max: 500),
            ],
            'positive' => [
                new Assert\Type('bool'),
            ],
            'visibility' => [
                new Assert\Choice(array_column(Visibility::cases(), 'value')),
            ]
        ];

        if (!$patch) {
            unset($fields['visibility']);
        }

        return $this->validate(
            $input,
            new Assert\Collection([
                'fields' => $fields,
                'allowMissingFields' => $patch,
            ])
        );
    }
}
