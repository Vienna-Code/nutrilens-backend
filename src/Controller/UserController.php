<?php

namespace App\Controller;

use App\Enum\AlimentaryRestriction;
use App\Enum\ReportType;
use App\Enum\UserRole;
use App\Repository\CommerceRepository;
use App\Repository\PostRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class UserController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private UserRepository $userRepository,
        private CommerceRepository $commerceRepository,
        private ProductRepository $productRepository,
        private PostRepository $postRepository,
        private ReviewRepository $reviewRepository,

        private UserManager $userManager,
    ) {}

    #[Route('/users', methods: ['GET'], name: 'app_users_list')]
    public function list(Request $request): JsonResponse
    {
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
                    'username' => [
                        new Assert\Type('string'),
                    ],
                    'email' => [
                        new Assert\Type('string'),
                    ],
                    'minPoints' => [
                        new Assert\Type(['type' => 'numeric']),
                    ],
                    'maxPoints' => [
                        new Assert\Type(['type' => 'numeric']),
                    ],
                    'page' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                    'orderBy' => [
                        new Assert\Choice([
                            'date_asc', 'date_desc',
                            'points_asc', 'points_desc',
                        ]),
                    ],
                ],
                'allowMissingFields' => true,
            ])
        );

        // Encontrar usuarios
        $users = $this->userRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $users
        ], 200, [], ['groups' => ['user:list']]);
    }

    #[Route('/users/stats', methods: ['GET'], name: 'app_users_stats')]
    public function stats(Request $request): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Obtener stats
        $total = $this->userRepository->countAll();
        $byRank = $this->userRepository->countByRank();
        $data = [
            'total' => $total,
            'bronze' => 0,
            'silver' => 0,
            'gold' => 0,
            'platinum' => 0,
        ];
        foreach ($byRank as $row) {
            $data[$row['rank']] = (int) $row['total'];
        }

        return $this->json([
            'data' => $data
        ], 200);
    }

    #[Route('/users/me/commerces', methods: ['GET'], name: 'app_users_list_commerces')]
    public function listCommerces(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Encontrar comercios que subió el usuario
        $commerces = $this->commerceRepository->findByReports($user, ReportType::SUBMISSION);

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/users', methods: ['POST'], name: 'app_users_post')]
    public function post(Request $request): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
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
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'username' => new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Length(min: 3, max: 40),
                        new Assert\Regex([
                            'pattern' => '/^[a-zA-Z0-9_.-]+$/',
                            'message' => 'Username can only contain letters, numbers, underscores (_), hyphens (-), and dots (.)',
                        ]),
                    ]),
                    'email' => new Assert\Required([
                        new Assert\Type('string'),
                        new Assert\Email
                    ]),
                    'password' => new Assert\Required([
                        new Assert\Type('string'),
                    ]),
                    'alimentaryRestrictions' => new Assert\Optional([
                        new Assert\Type('array'),
                        new Assert\Unique(),
                        new Assert\All([
                            new Assert\Choice(array_column(AlimentaryRestriction::cases(), 'value')),
                        ]),
                    ]),
                    'profilePicture' => new Assert\Optional([
                        new Assert\Type('string'),
                        new Assert\Uuid(),
                    ]),
                    'roles' => new Assert\Optional([
                        new Assert\Type('array'),
                        new Assert\Unique(),
                        new Assert\All([
                            new Assert\Choice(array_column(UserRole::cases(), 'value')),
                        ]),
                    ]),
                ],
            ])
        );
        
        // Crear usuario
        try {
            $user = $this->userManager->create($data, $user->getRoles());
        } catch (UniqueConstraintViolationException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'username_unique_idx')) {
                $msg = 'Un usuario bajo el nombre ' . $data['username'] . ' ya existe.';
            } elseif (str_contains($msg, 'email_unique_idx')) {
                $msg = 'El email ' . $data['email'] . ' ya está registrado.';
            } else {
                throw new LogicException('Undefined unique index checked.');
            }

            return $this->json(['error' => ['message' => $msg]], 409);
        }

        // Responder
        return $this->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => $user,
        ], 201, [], ['groups' => ['user:create']]);
    }

    #[Route('/users/{id}/commerces/stats', methods: ['GET'], name: 'app_users_list_commerces_stats')]
    public function listCommercesStats(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
        new Assert\Collection([
                'fields' => [
                    'id' => new Assert\AtLeastOneOf([
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

        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Usuario no encontrado.']
            ], 404);
        }

        // Obtener stats
        $total = $this->commerceRepository->countAllByUser($user);
        $byVerified = $this->commerceRepository->countByVerified($user);
        $data = [
            'total' => $total,
            'verified' => 0,
            'unverified' => 0,
        ];
        foreach ($byVerified as $row) {
            if ($row['verified']) {
                $data['verified'] = (int) $row['total'];
            } else {
                $data['unverified'] = (int) $row['total'];
            }
        }

        return $this->json([
            'data' => $data
        ], 200);
    }

    #[Route('/users/me/products', methods: ['GET'], name: 'app_users_list_products')]
    public function listProducts(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Encontrar productos que subió el usuario
        $products = $this->productRepository->findByReports($user, ReportType::SUBMISSION);

        // Responder
        return $this->json([
            'data' => $products
        ], 200, [], ['groups' => ['product:list:commerce']]);
    }

    #[Route('/users/{id}/products/stats', methods: ['GET'], name: 'app_users_list_products_stats')]
    public function listProductsStats(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
        new Assert\Collection([
                'fields' => [
                    'id' => new Assert\AtLeastOneOf([
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

        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Usuario no encontrado.']
            ], 404);
        }

        // Obtener stats
        $total = $this->productRepository->countAllByUser($user);
        $byVerified = $this->productRepository->countByVerified($user);
        $data = [
            'total' => $total,
            'verified' => 0,
            'unverified' => 0,
        ];
        foreach ($byVerified as $row) {
            if ($row['verified']) {
                $data['verified'] = (int) $row['total'];
            } else {
                $data['unverified'] = (int) $row['total'];
            }
        }

        return $this->json([
            'data' => $data
        ], 200);
    }

    #[Route('/users/me/reviews', methods: ['GET'], name: 'app_user_list_reviews')]
    public function listReviews(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Encontrar reseñas que subió el usuario
        $reviews = $this->reviewRepository->findBy(['user' => $user]);

        // Responder
        return $this->json([
            'data' => $reviews
        ], 200, [], ['groups' => ['review:list','review:list:me']]);
    }

    #[Route('/users/{id}/reviews/stats', methods: ['GET'], name: 'app_users_list_reviews_stats')]
    public function listReviewsStats(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
        new Assert\Collection([
                'fields' => [
                    'id' => new Assert\AtLeastOneOf([
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

        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Usuario no encontrado.']
            ], 404);
        }

        // Obtener stats
        $total = $this->reviewRepository->countAllByUser($user);
        $byPositive = $this->reviewRepository->countByPositive($user);
        $data = [
            'total' => $total,
            'positive' => 0,
            'negative' => 0,
        ];
        foreach ($byPositive as $row) {
            if ($row['positive']) {
                $data['positive'] = (int) $row['total'];
            } else {
                $data['negative'] = (int) $row['total'];
            }
        }

        return $this->json([
            'data' => $data
        ], 200);
    }

    #[Route('/users/me/posts', methods: ['GET'], name: 'app_user_list_posts')]
    public function listPosts(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Encontrar publicaciones que subió el usuario
        $posts = $this->postRepository->findBy(['user' => $user]);

        // Responder
        return $this->json([
            'data' => $posts
        ], 200, [], ['groups' => ['post:list']]);
    }

    #[Route('/users/{id}/posts/stats', methods: ['GET'], name: 'app_users_list_posts_stats')]
    public function listPostsStats(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
        new Assert\Collection([
                'fields' => [
                    'id' => new Assert\AtLeastOneOf([
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

        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Usuario no encontrado.']
            ], 404);
        }

        // Obtener stats
        $data = $this->postRepository->countAllByUser($user);
        $data = [
            'total' => $data[0]['total'],
            'totalViews' => (int) $data[0]['totalViews']
        ];

        return $this->json([
            'data' => $data
        ], 200);
    }

    #[Route('/users/{id}', methods: ['GET'], name: 'app_user_get')]
    public function get(string $id): JsonResponse
    {
        // Validación
        $this->validate(
            ['id' => $id],
        new Assert\Collection([
                'fields' => [
                    'id' => new Assert\AtLeastOneOf([
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

        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if (!$user) {
            return $this->json([
                'error' => ['message' => 'Usuario no encontrado.']
            ], 404);
        }

        return $this->json([
            'data' => $user
        ], 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/users/{id}', methods: ['PATCH'], name: 'app_user_patch')]
    public function patch(string $id, Request $request): JsonResponse
    {
        // Control de acceso
        $me = $this->getUser(); /** @var \App\Entity\User $user */
        if ($me === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        $user = ($id === 'me') ? $me : $this->userRepository->find($id);
        if (!\in_array('ROLE_ADMIN', $me->getRoles()) && $user !== $me) {
            return $this->json(['error' => [
                'message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'alimentaryRestrictions' => [
                        new Assert\Type('array'),
                        new Assert\Unique(),
                        new Assert\All([
                            new Assert\Choice(array_column(AlimentaryRestriction::cases(), 'value')),
                        ]),
                    ],
                    'profilePicture' => [
                        new Assert\Type('string'),
                        new Assert\Uuid(),
                    ],
                    'roles' => [
                        new Assert\Type('array'),
                        new Assert\Unique(),
                        new Assert\All([
                            new Assert\Choice(array_column(UserRole::cases(), 'value')),
                        ]),
                    ],
                    'currentPassword' => [
                        new Assert\Type('string'),
                    ],
                    'newPassword' => [
                        new Assert\Type('string'),
                    ],
                ],
                'allowMissingFields' => true,
            ])
        );

        // Modificar usuario
        $user = $this->userManager->update($data, $user, $me);
        
        // Responder
        return $this->json([
            'message' => 'Usuario modificado.',
            'data' => $user
        ], 200, [], ['groups' => ['user:update']]);
    }
}
