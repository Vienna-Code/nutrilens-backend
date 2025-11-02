<?php

namespace App\Controller;

use App\Dto\Commerce;
use App\Repository\CommerceRepository;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        $errors = $this->validation->validate(new Commerce\ListCommerces($data));
        if ($errors) return $errors;

        // Encontrar comercios
        $commerces = $this->commerceRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'app_commerce_get')]
    public function get(): JsonResponse
    {
        return $this->json([]);
    }

    #[Route('/', methods: ['POST'], name: 'app_commerce_post')]
    public function post(): JsonResponse
    {
        return $this->json([]);
    }
}
