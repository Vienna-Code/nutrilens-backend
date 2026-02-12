<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Service\CommentManager;
use App\Service\PostManager;
use App\Service\ValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class CommentController extends AbstractController
{
    public function __construct(
        private ValidationService $validation,
        private LoggerInterface $logger,

        private CommentRepository $commentRepository,
        private CommentManager $commentManager,

        private PostRepository $postRepository,
        private PostManager $postManager,

        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
    ) {}

    #[Route('/posts/{idp}/comments/{idc}', methods: ['GET'], name: 'app_comment_get')]
    public function get(int $idp, int $idc): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Encontrar comentario
        $comment = $this->commentRepository->findById($idc, $user);
        if ($comment === null) throw $this->createNotFoundException();

        // Responder
        return $this->json([
            'data' => $comment,
        ], 200, [], ['groups' => ['comment:read']]);
    }

    #[Route('/posts/{idp}/comments', methods: ['GET'], name: 'app_comment_list')]
    public function list(int $idp, Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Obtener parametros URL
        $data = $request->query->all();
        $data['post'] = $idp;

        // Validación con DTO
        // TODO

        // Encontrar comentarios
        $comments = $this->commentRepository->findByFilters($data, $user);

        // Responder
        return $this->json([
            'data' => $comments
        ], 200, [], ['groups' => ['comment:list']]);
    }

    #[Route('/posts/{idp}/comments', methods: ['POST'], name: 'app_comment_post')]
    public function post(int $idp, Request $request): JsonResponse
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

        // Crear comentario
        $post = $this->postRepository->find($idp);
        if (!$post) {
            return $this->json([
                'error' => ['message' => 'El post al que se trata de responder no existe.']
            ], 404);
        }
        if (isset($data['replyingTo'])) {
            $parent = $this->commentRepository->find($data['replyingTo']);
            if (!$parent) {
                return $this->json([
                    'error' => ['message' => 'El comentario al que se trata de responder no existe.']
                ], 404);
            }
            if ($parent->getPost()->getId() !== $idp) {
                return $this->json([
                    'error' => ['message' => "El comentario al que se trata de responder no se encuentra en el post de ID $idp."]
                ], 404);
            }
        } else {
            $parent = null;
        }
        $comment = $this->commentManager->create($data, $user, $post, $parent);

        // Responder
        return $this->json([
            'message' => 'Comentario creado correctamente.',
            'data' => $comment,
        ], 201, [], ['groups' => ['comment:create']]);
    }

    #[Route('/posts/{idp}/comments/{idc}', methods: ['PATCH'], name: 'app_comment_patch')]
    public function patch(
        #[MapEntity(mapping: ['idc' => 'id'])]
        Comment $comment,
        Request $request
    ): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }
        if ($user !== $comment->getUser() && !\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json([
                'error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);

        // Actualizar comentario
        $comment = $this->commentManager->update($data, $comment, $user);

        // Responder
        return $this->json([
            'message' => 'Comentario actualizado correctamente.',
            'data' => $comment,
        ], 201, [], ['groups' => ['comment:update']]);
    }

    #[Route('/posts/{idp}/comments/{idc}', methods: ['DELETE'], name: 'app_comment_delete')]
    public function delete(
        #[MapEntity(mapping: ['idc' => 'id'])]
        Comment $comment
    ): JsonResponse {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Eliminar comentario
        $this->commentManager->delete($comment);

        return $this->json([
            'message' => 'Comentario eliminado.'
        ], 200);
    }
}
