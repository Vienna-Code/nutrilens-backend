<?php

namespace App\Controller;

use App\Enum\Visibility;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Service\CommentManager;
use App\Service\PostManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommentController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private CommentRepository $commentRepository,
        private CommentManager $commentManager,

        private PostRepository $postRepository,
        private PostManager $postManager,

        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
    ) {}

    #[Route('/posts/{idp}/comments/{idc}', methods: ['GET'], name: 'app_comment_get')]
    public function get(string $idp, string $idc): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Validación
        $this->validate(
            ['idp' => $idp, 'idc' => $idc],
            new Assert\Collection([
                'fields' => [
                    'idp' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'idc' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar comentario
        $comment = $this->commentRepository->findById($idc, $idp, $user);
        if (!$comment) {
            return $this->json([
                'error' => ['message' => 'Comentario no encontrado.']
            ], 404);
        }

        // Responder
        return $this->json([
            'data' => $comment,
        ], 200, [], ['groups' => ['comment:read']]);
    }

    #[Route('/posts/{idp}/comments', methods: ['GET'], name: 'app_comment_list')]
    public function list(string $idp, Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Obtener parametros URL
        $data = $request->query->all();
        $data['post'] = $idp;

        // Validación
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'post' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'page' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
                'allowMissingFields' => true,
            ])
        );

        // Encontrar comentarios
        $comments = $this->commentRepository->findByFilters($data, $user);

        // Responder
        return $this->json([
            'data' => $comments
        ], 200, [], ['groups' => ['comment:list']]);
    }

    #[Route('/posts/{idp}/comments', methods: ['POST'], name: 'app_comment_post')]
    public function post(string $idp, Request $request): JsonResponse
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
        $data['postId'] = $idp;

        // Validación
        $data = $this->validateComment($data);

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
    public function patch(string $idp, string $idc, Request $request): JsonResponse {
        // Control de acceso
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json([
                'error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']
            ], 401);
        }

        // Parseo del request JSON
        $data = json_decode($request->getContent(), true);
        $data['postId'] = $idp;

        // Validación
        $data = $this->validateComment($data, true);

        // Encontrar comentario
        $comment = $this->commentRepository->findById($idc, $idp, $user);
        if (!$comment) {
            return $this->json([
                'error' => ['message' => 'Comentario no encontrado.']
            ], 404);
        }
        if ($user !== $comment->getUser() && !\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json([
                'error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

        // Actualizar comentario
        $comment = $this->commentManager->update($data, $comment, $user);

        // Responder
        return $this->json([
            'message' => 'Comentario actualizado correctamente.',
            'data' => $comment,
        ], 201, [], ['groups' => ['comment:update']]);
    }

    #[Route('/posts/{idp}/comments/{idc}', methods: ['DELETE'], name: 'app_comment_delete')]
    public function delete(string $idp, string $idc): JsonResponse {
        // Control de acceso (SOLO ADMINS)
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        if ($user === null) {
            return $this->json(['error' => ['message' => 'Se requiere autenticación para acceder a este endpoint.']], 401);
        }
        if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']], 403);
        }

        // Validación
        $this->validate(
            ['idp' => $idp, 'idc' => $idc],
            new Assert\Collection([
                'fields' => [
                    'idp' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'idc' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar comentario
        $comment = $this->commentRepository->findById($idc, $idp, $user);
        if (!$comment) {
            return $this->json([
                'error' => ['message' => 'Comentario no encontrado.']
            ], 404);
        }

        // Eliminar comentario
        $this->commentManager->delete($comment);

        return $this->json([
            'message' => 'Comentario eliminado.'
        ], 200);
    }

    private function validateComment(array $input, bool $patch = false)
    {
        $fields = [
            'postId' => [
                new Assert\Type('digit'),
                new Assert\Positive(),
            ],
            'content' => [
                new Assert\Type('string'),
                new Assert\Length(max: 5000),
            ],
            'replyingTo' => new Assert\Optional([
                new Assert\Sequentially([
                    new Assert\Type(['type' => 'digit']),
                    new Assert\Positive(),
                ]),
                new Assert\IsNull(),
            ]),
            'visibility' => [
                new Assert\Type('string'),
                new Assert\Choice(array_column(Visibility::cases(), 'value')),
            ]
        ];

        if (!$patch) {
            unset($fields['visibility']);
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
