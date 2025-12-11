<?php

namespace App\Controller;

use App\Dto\Reviews;
use App\Entity\Commerce;
use App\Entity\Review;
use App\Repository\CommerceRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewVoteRepository;
use App\Service\ReviewManager;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Json;

final class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private NormalizerInterface $normalizer,

        private ReviewRepository $reviewRepository,
        private CommerceRepository $commerceRepository,
        private ReviewManager $rm,

        private ReviewVoteRepository $rvRepository,
    ) {}

    #[Route('/commerces/{idc}/reviews/{idr}', methods: ['GET'], name: 'app_review_get')]
    public function get(int $idc, string $idr): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        
        // Encontrar reseña
        if ($idr === 'me') {
            $commerce = $this->commerceRepository->find($idc);
            if (!$user || !$commerce) {
                throw $this->createNotFoundException();
            }
            $review = $this->reviewRepository->findOneBy([
                'commerce' => $commerce,
                'user' => $user
            ]);
        } else {
            $review = $this->reviewRepository->find((int)$idr);
        }
        if ($review === null) throw $this->createNotFoundException();

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
        ], 200, [], ['groups' => ['review:update']]);
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

    #[Route('/commerces/{idc}/reviews/{idr}/vote', methods: ['PATCH'], name: 'app_review_vote_patch')]
    public function votePatch(
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
        if ($review->getUser() === $user) {
            return $this->json([
                'error' => ['message' => 'No puedes votar tu propia reseña']
            ], 400);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación con DTO
        // TODO

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
}
