<?php

namespace App\Controller;

use App\Dto\Auth;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Service\ValidationService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/auth')]
final class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidationService $validation,
        private LoggerInterface $logger,
    ) {}

    #[Route('/signup', methods: ['POST'], name: 'app_auth_signup')]
    public function signUp(
        Request $request,
        Security $security,
    ): JsonResponse {
        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        
        // Validación con DTO
        $errors = $this->validation->validate(new Auth\SignUp($data));
        if ($errors) return $errors;
        
        // Crear usuario
        try {
            $user = UserFactory::new()->create([
                'username' => $data['username'],
                'password' => $data['password'],
                'email' => $data['email'],
                'alimentaryRestrictions' => [],
    
                // TEMPORAL HASTA QUE HAYA VERIFICACIÓN CON MAIL
                'roles' => ['ROLE_USER'],
                'verification' => null,
            ]);
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'username_unique_idx')) {
                $msg = 'Un usuario bajo el nombre ' . $data['username'] . ' ya existe.';
            } elseif (str_contains($msg, 'email_unique_idx')) {
                $msg = 'El email ' . $data['email'] . ' ya está registrado.';
            } else {
                throw new LogicException('Undefined unique index checked.');
            }

            return $this->json(['error' => ['message' => $msg]], 409);
        }

        // TODO: Verificación con mail
        $security->login($user);

        // Responder
        return $this->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => $user,
        ], 201, [], ['groups' => ['user:create']]);
    }

    #[Route('/login', methods: ['POST'], name: 'app_auth_login')]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        Security $security
    ): JsonResponse {
        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación con DTO
        $errors = $this->validation->validate(new Auth\LogIn($data));
        if ($errors) return $errors;

        // Cargar Usuario
        $user = $userRepository->findOneByIdentifier('username', $data['username']);
        if (!$user) {
            return $this->invalidCredentialsMessage();
        }
        if ($user->getVerification() !== null) {
            return $this->json([
                'error' => ['message' => 'Usuario aún no verificado con email.']
            ], 401);
        }

        // Login
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->invalidCredentialsMessage();
        }
        $security->login($user);

        // Responder
        return $this->json([
            'message' => 'Ingreso exitoso.',
            'data' => $user,
        ], 200, [], ['groups' => ['user:create']]);
    }

    #[Route('/me', methods: ['GET'], name: 'app_auth_me')]
    public function me(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->json([
                'error' => ['message' => 'No autenticado.']
            ], 401);
        }

        return $this->json([
            'data' => $user,
        ], 200, [], ['groups' => ['user:create']]);
    }
    
    #[Route('/logout-success', name: 'app_auth_logout_success')]
    public function logOutSuccess(Request $request): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function invalidCredentialsMessage(): JsonResponse
    {
        return $this->json([
            'error' => ['message' => 'Credenciales inválidas.']
        ], 401);
    }

}
