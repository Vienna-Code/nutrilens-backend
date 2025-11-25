<?php

namespace App\Controller;

use App\Dto\Reviews;
use App\Entity\Commerce;
use App\Entity\Review;
use App\Repository\CommerceRepository;
use App\Repository\ReviewRepository;
use App\Service\ReviewManager;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;

final class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private ReviewRepository $reviewRepository,
        private CommerceRepository $commerceRepository,
        private ReviewManager $rm,
    ) {}

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['GET'], name: 'app_review_get')]
    public function get(
        #[MapEntity(mapping: ['idr' => 'id'])]
        Review $review,
    ): JsonResponse {
        // Responder
        return $this->json([
            'data' => $review
        ], 200, [], ['groups' => ['review:read']]);
    }

    #[Route('/commerces/{idc}/reviews', methods: ['GET'], name: 'app_review_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();
        $data['commerce'] = $request->attributes->get('idc');

        // Validación con DTO
        $errors = $this->validation->validate(new Reviews\ListReviews($data));
        if ($errors) return $errors;

        // Encontrar reviews
        $reviews = $this->reviewRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $reviews
        ], 200, [], ['groups' => ['review:list']]);
    }

    #[Route('/commerces/{idc}/reviews', methods: ['POST'], name: 'app_review_post')]
    public function post(
        #[MapEntity(mapping: ['idc' => 'id'])]
        Commerce $commerce,
        Request $request
    ): JsonResponse {
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
    public function patch(
        //#[MapEntity(mapping: ['idc' => 'id'])]
        //Commerce $commerce,
        #[MapEntity(mapping: ['idr' => 'id'])]
        Review $review,
        Request $request,
    ): JsonResponse {
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
        ], 201, [], ['groups' => ['review:update']]);
    }

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['DELETE'], name: 'app_review_delete')]
    public function delete(
        //#[MapEntity(mapping: ['idc' => 'id'])]
        //Commerce $commerce,
        #[MapEntity(mapping: ['idr' => 'id'])]
        Review $review,
        Request $request,
    ): JsonResponse {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Eliminar reseña
        $this->rm->delete($review);

        return $this->json([
            'message' => 'Reseña eliminada.'
        ], 200);
    }
}
