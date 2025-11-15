<?php

namespace App\Controller;

use App\Dto\Products;
use App\Repository\ProductRepository;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private ProductRepository $productRepository,
    ) {}

    #[Route('/products', methods: ['GET'], name: 'app_product_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        $errors = $this->validation->validate(new Products\ListProducts($data));
        if ($errors) return $errors;

        // Encontrar comercios
        $commerces = $this->productRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['product:list']]);
    }
}
