<?php

namespace App\Controller;

use App\Enum\AlimentaryRestriction;
use App\Enum\ReportType;
use App\Repository\CommerceRepository;
use App\Repository\PostRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\UserManager;
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

    #[Route('/users/me/commerces/stats', methods: ['GET'], name: 'app_users_list_commerces_stats')]
    public function listCommercesStats(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
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

    #[Route('/users/me/products/stats', methods: ['GET'], name: 'app_users_list_products_stats')]
    public function listProductsStats(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
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

    #[Route('/users/me/reviews/stats', methods: ['GET'], name: 'app_users_list_reviews_stats')]
    public function listReviewsStats(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
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

    #[Route('/users/me/posts/stats', methods: ['GET'], name: 'app_users_list_posts_stats')]
    public function listPostsStats(): JsonResponse
    {
        // Autenticación
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
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
                ],
                'allowMissingFields' => true,
            ])
        );

        // Modificar usuario
        $user = $this->userManager->update($data, $user);
        
        // Responder
        return $this->json([
            'message' => 'Usuario modificado.',
            'data' => $user
        ], 200, [], ['groups' => ['user:update']]);
    }
}
