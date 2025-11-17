<?php

namespace App\Controller;

use App\Dto\Commerces;
use App\Entity\Commerce;
use App\Repository\CommerceRepository;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CommerceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private CommerceRepository $commerceRepository,
    ) {}

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

        // Setear rating
        foreach ($commerces as &$commerce) {
            $commerce[0]->setRating($commerce['reviewsCount'],$commerce['positiveCount']);
            $commerce = $commerce[0];
        }

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/commerces/{id}', methods: ['GET'], name: 'app_commerce_get')]
    public function get(int $id): JsonResponse
    {
        // Encontrar comercio
        $commerce = $this->commerceRepository->findOneById($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }
        
        // Setear rating
        $commerce[0]->setRating($commerce['reviewsCount'],$commerce['positiveCount']);
        $commerce = $commerce[0];

        // Responder
        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }

    #[Route('/commerces', methods: ['POST'], name: 'app_commerce_post')]
    public function post(): JsonResponse
    {
        return $this->json([]);
    }
}
