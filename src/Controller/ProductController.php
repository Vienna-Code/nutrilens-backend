<?php

namespace App\Controller;

use App\Dto\Products;
use App\Repository\ProductRepository;
use App\Repository\CommerceRepository;
use App\Service\ValidationService;
use App\Service\ProductManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private ProductManager $productManager,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private ProductRepository $productRepository,
        private CommerceRepository $commerceRepository,
    ) {}

    #[Route('/products', methods: ['GET'], name: 'app_product_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        $errors = $this->validation->validate(new Products\ListProducts($data));
        if ($errors) return $errors;

        // Encontrar productos
        $products = $this->productRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $products
        ], 200, [], ['groups' => ['product:list']]);
    }

    #[Route('/products', methods: ['POST'], name: 'app_product_post')]
    public function post(Request $request)
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

        // Validacion con DTO
        // TODO

        // Encontrar comercio
        $commerce = $this->commerceRepository->findOneById($data['commerceId']);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Agregar producto al comercio
        $product = $this->productManager->create($data, $user, $commerce);

        // Responder
        return $this->json([
            'message' => 'Producto registrado correctamente.',
            'data' => $product,
        ], 201, [], ['groups' => ['product:create']]);
    }
}
