<?php

namespace App\Controller;

use App\Dto\Commerces;
use App\Dto\_NestedObjects\ContactInfo;
use App\Dto\_NestedObjects\CommerceSchedule;
use App\Enum\UserRank;
use App\Repository\CommerceRepository;
use App\Service\CommerceManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CommerceController extends AbstractController
{
    public function __construct(
        private CommerceManager $commerceManager,
        private ValidationService $validation,
        private LoggerInterface $logger,
        private CommerceRepository $commerceRepository,
    ) {}

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

        // Responder
        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }

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

        // Responder
        return $this->json([
            'data' => $commerces
        ], 200, [], ['groups' => ['commerce:list']]);
    }

    #[Route('/commerces', methods: ['POST'], name: 'app_commerce_post')]
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
        
        // Validación con DTO
        $errors = $this->validation->validate(new Commerces\PostCommerces($data));
        if ($errors) return $errors;
        $errors = $this->validation->validate(new ContactInfo($data['contactInfo']));
        if ($errors) return $errors;
        foreach ($data['commerceSchedules'] as $commerceSchedule) {
            $errors = $this->validation->validate(new CommerceSchedule($commerceSchedule));
        }
        if ($errors) return $errors;

        // Crear comercio, horarios & reportes
        $data['verifiedUser'] =
            $user->getUserRank() === UserRank::PLATINUM || // Usuario con rango platino
            \in_array('ROLE_ADMIN', $user->getRoles()); // Administrador
        $commerce = $this->commerceManager->create($data, $user);
        
        // Responder
        return $this->json([
            'message' => 'Comercio registrado correctamente.',
            'data' => $commerce,
        ], 201, [], ['groups' => ['commerce:create']]);
    }

    #[Route('/commerces/{id}', methods: ['PATCH'], name: 'app_commerce_patch')]
    public function patch(int $id, Request $request): JsonResponse
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
        $commerce = $this->commerceRepository->findOneById($id);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'Comercio no encontrado.']
            ], 404);
        }

        // Modificar comercio
        $commerce = $this->commerceManager->update($data, $commerce, $user);
        if (!$commerce) {
            return $this->json([
                'error' => ['message' => 'No tiene la autoridad para actualizar este comercio.']
            ], 403);
        }

        // Responder
        return $this->json([
            'data' => $commerce
        ], 200, [], ['groups' => ['commerce:read']]);
    }
}
