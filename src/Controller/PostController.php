<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\PostVoteRepository;
use App\Service\PostManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class PostController extends AbstractController
{
    public function __construct(
        private ValidationService $validation,
        private LoggerInterface $logger,

        private PostRepository $postRepository,
        private PostManager $postManager,
        
        private PostVoteRepository $pvRepository,

        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
    ) {}

    #[Route('/posts/{id}', methods: ['GET'], name: 'app_post_get')]
    public function get(int $id): JsonResponse
    {
        // Encontrar post
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        $post = $this->postRepository->findById($id, $user);

        $postData = $this->normalizer->normalize($post, context: [
            'groups' => ['post:read']
        ]);

        // Agregar si le diste like o no
        if ($user) {
            $vote = $this->pvRepository->findOneBy([
                'post' => $post,
                'user' => $user,
            ]);
            if ($vote) {
                $vote = $vote->isPositive();
            }
            $postData['liked'] = $vote;

            // Agregar visita
            if ($user !== $post->getUser()) {
                $this->postManager->addView($post);
            }
        } else {
            $postData['liked'] = null;
        }

        // Responder
        return $this->json([
            'data' => $postData
        ], 200, [], ['groups' => ['post:read']]);
    }

    #[Route('/posts', methods: ['GET'], name: 'app_post_list')]
    public function list(Request $request): JsonResponse
    {
        // Obtener parametros URL
        $data = $request->query->all();

        // Validación con DTO
        // TODO

        // Encontrar posts
        $posts = $this->postRepository->findByFilters($data);

        // Responder
        return $this->json([
            'data' => $posts
        ], 200, [], ['groups' => ['post:list']]);
    }

    #[Route('/posts', methods: ['POST'], name: 'app_post_post')]
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

        // Crear publicación
        $post = $this->postManager->create($data, $user);
        if (\is_string($post)) {
            return $this->json([
                'error' => ['message' => "No se creo la publicación: la etiqueta $post no existe."]
            ], 400);
        }
        
        // Responder
        return $this->json([
            'message' => 'Publicación creada correctamente.',
            'data' => $post,
        ], 201, [], ['groups' => ['post:create']]);
    }

    #[Route('/posts/{id}', methods: ['PATCH'], name: 'app_post_patch')]
    public function patch(Post $post, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if ($user !== $post->getUser() && !\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json([
                'error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validacion con DTO
        // TODO

        // Modificar publicación
        $post = $this->postManager->update($data, $post, $user);
        if (\is_string($post)) {
            return $this->json([
                'error' => ['message' => "No se creo la publicación: la etiqueta $post no existe."]
            ], 400);
        }

        // Responder
        return $this->json([
            'message' => 'Publicación modificada.',
            'data' => $post
        ], 200, [], ['groups' => ['post:update']]);
    }

    #[Route('/posts/{id}', methods: ['DELETE'], name: 'app_post_delete')]
    public function delete(Post $post): JsonResponse
    {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Eliminar publicación
        $this->postManager->delete($post);

        return $this->json([
            'message' => 'Publicación eliminada.'
        ], 200);
    }

    #[Route('/posts/{id}/vote', methods: ['PATCH'], name: 'app_post_vote_patch')]
    public function votePatch(Post $post, Request $request): JsonResponse
    {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if ($post->getUser() === $user) {
            return $this->json([
                'error' => ['message' => 'No puedes votar tu propia publicación']
            ], 400);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Validación con DTO
        // TODO

        // Agregar voto
        if ($this->postManager->vote($post, $user, $data['positive'])) {
            return $this->json([
                'message' => 'Se votó la publicación correctamente.'
            ], 200);
        } else {
            return $this->json([
                'message' => 'La publicación ya estaba votada de esta manera, no se realizaron cambios.'
            ], 200);
        }
    }
}
