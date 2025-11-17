<?php

namespace App\Controller;

use App\Dto\Reviews;
use App\Repository\ReviewRepository;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private ReviewRepository $reviewRepository,
    ) {}

    #[Route('/reviews', methods: ['GET'], name: 'app_review_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

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
}
