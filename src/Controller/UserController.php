<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    public function __construct(
        private ValidationService $validationService,
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private UserManager $userManager,
    ) {}

    #[Route('/users/{id}', methods: ['GET'], name: 'app_user_get')]
    public function get(string $id): JsonResponse
    {
        // Encontrar usuario
        $user = ($id === 'me') ? $this->getUser() : $this->userRepository->find($id);
        if ($user === null) throw $this->createNotFoundException();

        return $this->json([
            'data' => $user
        ], 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/users/{id}', methods: ['PATCH'], name: 'app_user_patch')]
    public function patch(string $id, Request $request): JsonResponse
    {
        // Control de acceso
        $me = $this->getUser(); /** @var \App\Entity\User $user */
        if ($me === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        $user = ($id === 'me') ? $me : $this->userRepository->find($id);
        if (!\in_array('ROLE_ADMIN', $me->getRoles()) && $user !== $me) {
            return $this->json(['error' => [
                'message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación con DTO
        // TODO

        // Modificar usuario
        $user = $this->userManager->update($data, $user);
        
        // Responder
        return $this->json([
            'message' => 'Usuario modificado.',
            'data' => $user
        ], 200, [], ['groups' => ['user:update']]);
    }
}
