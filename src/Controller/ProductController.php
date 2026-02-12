<?php

namespace App\Controller;

use App\Dto\Products;
use App\Entity\Product;
use App\Enum\AlimentaryRestriction;
use App\Enum\ProductCategory;
use App\Enum\ReportType;
use App\Repository\ProductRepository;
use App\Repository\CommerceRepository;
use App\Repository\ProductReportRepository;
use App\Service\ValidationService;
use App\Service\ProductManager;
use App\Service\ProductReportManager;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private ProductRepository $productRepository,
        private ProductManager $productManager,

        private ProductReportRepository $pReportRepository,
        private ProductReportManager $pReportManager,

        private CommerceRepository $commerceRepository,
        private NormalizerInterface $normalizer,
    ) {}

    #[Route('/products/{id}', methods: ['GET'], name: 'app_product_get')]
    public function get(string $id): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Obtener producto
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json([
                'error' => ['message' => 'Producto no encontrado.']
            ], 404);
        }

        $product = $this->normalizer->normalize($product, context: [
            'groups' => ['product:read']
        ]);

        // Agregar si fue reportado como verificado o no
        if ($user) {
            $reports = $this->pReportRepository->findBy([
                'product' => $product,
                'user' => $user,
            ]);
            foreach ($reports as $report) {
                $vote = match ($report->getType()) {
                    ReportType::CONFIRMATION => true,
                    ReportType::REBUTTAL     => false,
                    default                  => $vote ?? null,
                };
            }
            $product['userVerificationReport'] = $vote ?? null;
        }

        // Responder
        return $this->json([
            'data' => $product
        ], 200, [], ['groups' => ['product:read']]);
    }

    #[Route('/products', methods: ['GET'], name: 'app_product_list')]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Obtener parametros URL
        $data = $request->query->all();

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'commerce' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                    'unverified' => [],
                ],
                'allowMissingFields' => true,
            ])
        );

        // Encontrar productos
        $products = $this->productRepository->findByFilters($data);

        // Agregar si fue reportado como verificado o no
        foreach ($products as &$product) {
            $product = $this->normalizer->normalize($product, context: [
                'groups' => ['product:list']
            ]);

            if ($user) {
                $reports = $this->pReportRepository->findBy([
                    'product' => $product,
                    'user' => $user,
                ]);
                foreach ($reports as $report) {
                    $vote = match ($report->getType()) {
                        ReportType::CONFIRMATION => true,
                        ReportType::REBUTTAL     => false,
                        default                  => $vote ?? null,
                    };
                }
                $product['userVerificationReport'] = $vote ?? null;
            }
        }

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

        // Validación
        $data = $this->validateProduct($data);

        // Encontrar comercio
        $commerce = $this->commerceRepository->find($data['commerceId']);
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

        // Validación
        $data = $this->validateProduct($data, true);

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
    public function delete(string $id): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para aceder a este endpoint.']], 403);
        }

        // Validación
        $this->validate(
            ['id' => $id],
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Obtener producto
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json([
                'error' => ['message' => 'Producto no encontrado.']
            ], 404);
        }

        // Eliminar producto
        $this->productManager->delete($product);

        return $this->json([
            'message' => 'Producto eliminado.'
        ], 200);
    }

    #[Route('/products/{idp}/reports/{idr}', methods: ['GET'], name: 'app_productreport_get')]
    public function getReport(int $idp, int $idr): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Encontrar reporte
        $report = $this->pReportRepository->find($idr);
        if (!$report) {
            return $this->json([
                'error' => ['message' => 'Reporte no encontrado.']
            ], 404);
        }

        return $this->json([
            'data' => $report
        ], 200, [], ['groups' => ['productreport:read']]);
    }

    #[Route('/products/{id}/reports', methods: ['GET'], name: 'app_productreport_list')]
    public function listReports(int $id, Request $request): JsonResponse
    {
        // Control de acceso
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
        // TODO

        // Encontrar reportes
        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json([
                'error' => ['message' => 'Producto no encontrado.']
            ], 404);
        }
        $reports = $this->pReportRepository->findByFilters($data, $product);

        // Responder
        return $this->json([
            'data' => $reports
        ], 200, [], ['groups' => ['productreport:list']]);
    }

    #[Route('/products/{id}/reports', methods: ['POST'], name: 'app_productreport_create')]
    public function createReport(int $id, Request $request): JsonResponse
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
        $type = ReportType::tryFrom($data['type']);

        // Encontrar producto
        $product = $this->productRepository->findWithReports($id);
        if (!$product) {
            return $this->json([
                'error' => ['message' => 'Producto no encontrado.']
            ], 404);
        }

        // Verificación extra
        // No reportar productos que subiste
        if ($product->getProductReports()->exists(
            fn ($key, $report) => $report->getUser() === $user && $report->getType() === ReportType::SUBMISSION
        )) {
            return $this->json([
                'error' => ['message' => 'No puedes reportar productos que hayas subido.']
            ], 403);
        }
        // Chequeo de existencia
        if ($product->getProductReports()->exists(
            fn ($key, $report) =>
                $report->getUser() === $user &&
                $report->getType() === $type &&
                $report->getType() !== ReportType::ISSUE
        )) {
            return $this->json([
                'error' => ['message' => 'Ya subiste este reporte.']
            ], 409);
        }
        
        // Validación
        // TODO
        if (!\in_array(ReportType::tryFrom($data['type']), [
            ReportType::CONFIRMATION,
            ReportType::REBUTTAL,
            ReportType::ISSUE
        ])) {
            return $this->json([
                'error' => ['message' => 'Tipo de reporte inválido.']
            ], 400);
        }

        // Crear reporte
        $report = $this->pReportManager->create($data, $product, $user);
        
        // Responder
        return $this->json([
            'message' => 'Reporte de producto creado correctamente.',
            'data' => $report,
        ], 201, [], ['groups' => ['productreport:create']]);
    }

    #[Route('/products/{idp}/reports/{idr}', methods: ['PATCH'], name: 'app_productreport_patch')]
    public function patchReport(int $idp, int $idr, Request $request): JsonResponse
    {
        // Control de acceso
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
        // TODO

        // Actualizar reporte
        $report = $this->pReportRepository->find($idr);
        if (!$report) {
            return $this->json([
                'error' => ['message' => 'Reporte no encontrado.']
            ], 404);
        }
        $report = $this->pReportManager->update($data, $report);

        // Responder
        return $this->json([
            'message' => 'Reporte de producto actualizado correctamente.',
            'data' => $report,
        ], 200, [], ['groups' => ['productreport:update']]);
    }

    private function validateProduct(array $input, bool $patch = false): array {
        $fields = [
            'commerceId' => [
                new Assert\Type('integer'),
                new Assert\Positive(),
            ],
            'name' => [
                new Assert\Type('string'),
                new Assert\Length(max: 50),
            ],
            'brand' => [
                new Assert\Type('string'),
                new Assert\Length(max: 50),
            ],
            'category' => [
                new Assert\Choice(array_column(ProductCategory::cases(), 'value')),
            ],
            'price' => [
                new Assert\Type('numeric'),
                new Assert\Positive(),
            ],
            'aptFor' => [
                new Assert\Type('array'),
                new Assert\Unique(),
                new Assert\All([
                    new Assert\Choice(array_column(AlimentaryRestriction::cases(), 'value')),
                ]),
            ],
            'verified' => [
                new Assert\Type('bool'),
            ]
        ];

        if (!$patch) {
            unset($fields['verified']);
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