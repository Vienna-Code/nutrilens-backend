<?php

namespace App\Controller;

use App\Dto\Products;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CommerceRepository;
use App\Service\ValidationService;
use App\Service\ProductManager;
use Closure;
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

    #[Route('/products/{id}', methods: ['GET'], name: 'app_product_get')]
    public function get(Product $product): JsonResponse
    {
        // Responder
        return $this->json([
            'data' => $product
        ], 200, [], ['groups' => ['product:read']]);
    }

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

        // Validacion con DTO
        // TODO

        // Encontrar producto
        $commerce = $this->commerceRepository->findOneById($data['commerceId']);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Producto no encontrado.']
            ], 404);
        }

        // Agregar producto al producto
        $product = $this->productManager->create($data, $user, $commerce);

        // Responder
        return $this->json([
            'message' => 'Producto registrado correctamente.',
            'data' => $product,
        ], 201, [], ['groups' => ['product:create']]);
    }

    #[Route('/products/{id}', methods: ['PATCH'], name: 'app_product_patch')]
    public function patch(Product $product, Request $request)
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

        // Modificar producto
        $product = $this->productManager->update($data, $product, $user);
        if (!$product) {
            return $this->json([
                'error' => ['message' => 'No tiene la autoridad para actualizar este producto.']
            ], 403);
        }

        // Responder
        return $this->json([
            'message' => 'Producto modificado.',
            'data' => $product
        ], 200, [], ['groups' => ['product:update']]);
    }

    #[Route('/products/{id}', methods: ['DELETE'], name: 'app_product_delete')]
    public function delete(Product $product): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para aceder a este endpoint.']], 403);
        }

        // Eliminar producto
        $this->productManager->delete($product);

        return $this->json([
            'message' => 'Producto eliminado.'
        ], 200);
    }
}