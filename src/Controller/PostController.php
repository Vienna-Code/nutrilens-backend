<?php

namespace App\Controller;

use App\Enum\Visibility;
use App\Repository\PostRepository;
use App\Repository\PostVoteRepository;
use App\Service\PostManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PostController extends ApiController
{
    public function __construct(
        protected ValidatorInterface $validator,
        private LoggerInterface $logger,

        private PostRepository $postRepository,
        private PostManager $postManager,
        
        private PostVoteRepository $pvRepository,

        private SerializerInterface $serializer,
        private NormalizerInterface $normalizer,
    ) {}

    #[Route('/posts/{id}', methods: ['GET'], name: 'app_post_get')]
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

        // Encontrar post
        $post = $this->postRepository->findById((int)$id, $user);
        if (!$post) {
            return $this->json([
                'error' => ['message' => 'Publicación no encontrada.']
            ], 404);
        }

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
        ], 200);
    }

    #[Route('/posts', methods: ['GET'], name: 'app_post_list')]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */

        // Obtener parametros URL
        $data = $request->query->all();

        // Validación
        if (isset($data['visibility'])) {
            if (!\in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->json(['error' => ['message' => 'No tienes permisos suficientes para utilizar el atributo "visibility"']], 403);
            }
            $data['visibility'] = array_filter(array_map('trim', explode(',', $data['visibility'])));
        }
        $data = $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'visibility' => [
                        new Assert\Type('array'),
                        new Assert\All([
                            new Assert\Choice(array_column(Visibility::cases(), 'value')),
                        ]),
                    ],
                    'page' => [
                        new Assert\Type(['type' => 'digit']),
                        new Assert\Positive(),
                    ],
                ],
                'allowExtraFields' => true,
                'allowMissingFields' => true,
            ])
        );

        // Encontrar posts
        $posts = $this->postRepository->findByFilters($data, $user);

        // Agregar si les diste like o no
        foreach ($posts as &$post) {
            $post = $this->normalizer->normalize($post, context: [
                'groups' => ['post:list']
            ]);

            if ($user) {
                $vote = $this->pvRepository->findOneBy([
                    'post' => $post,
                    'user' => $user,
                ]);
                if ($vote) {
                    $vote = $vote->isPositive();
                }
                $post['liked'] = $vote;
            } else {
                $post['liked'] = null;
            }
        }

        // Responder
        return $this->json([
            'data' => $posts
        ], 200);
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

        // Validación
        $this->validatePost($data);

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
    public function patch(string $id, Request $request): JsonResponse
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
        $this->validatePost($data, true);

        // Encontrar post
        $post = $this->postRepository->findById((int)$id, $user);
        if (!$post) {
            return $this->json([
                'error' => ['message' => 'Publicación no encontrada.']
            ], 404);
        }
        if ($user !== $post->getUser() && !\in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json([
                'error' => ['message' => 'No tienes permisos suficientes para acceder a este endpoint.']
            ], 403);
        }

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
    public function delete(string $id): JsonResponse
    {
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

        // Encontrar post
        $post = $this->postRepository->findById((int)$id, $user);
        if (!$post) {
            return $this->json([
                'error' => ['message' => 'Publicación no encontrada.']
            ], 404);
        }

        // Eliminar publicación
        $this->postManager->delete($post);

        return $this->json([
            'message' => 'Publicación eliminada.'
        ], 200);
    }

    #[Route('/posts/{id}/vote', methods: ['PATCH'], name: 'app_post_vote_patch')]
    public function votePatch(string $id, Request $request): JsonResponse
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
        $data['id'] = $id;

        // Validación
        $this->validate(
            $data,
            new Assert\Collection([
                'fields' => [
                    'id' => [
                        new Assert\Type('digit'),
                        new Assert\Positive(),
                    ],
                    'positive' => [
                        new Assert\AtLeastOneOf([
                            new Assert\Type('bool'),
                            new Assert\IsNull(),
                        ]),
                    ],
                ],
                'allowExtraFields' => true,
            ])
        );

        // Encontrar post
        $post = $this->postRepository->findById((int)$id, $user);
        if (!$post) {
            return $this->json([
                'error' => ['message' => 'Publicación no encontrada.']
            ], 404);
        }
        if ($post->getUser() === $user) {
            return $this->json([
                'error' => ['message' => 'No puedes votar tu propia publicación']
            ], 400);
        }

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

    private function validatePost(array $input, bool $patch = false): array
    {
        $fields = [
            'title' => [
                new Assert\Type('string'),
                new Assert\Length(max: 100),
            ],
            'content' => [
                new Assert\Type('string'),
                new Assert\Length(max: 5000),
            ],
            'visibility' => [
                new Assert\Type('string'),
                new Assert\Choice(array_column(Visibility::cases(), 'value')),
            ],
            'tags' => [
                new Assert\Type('array'),
                new Assert\Unique(),
                new Assert\All([
                    new Assert\Type('string'),
                ]),
            ],
            'attachments' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\Uuid(),
                ]),
            ]),
        ];

        return $this->validate(
            $input,
            new Assert\Collection([
                'fields' => $fields,
                'allowMissingFields' => $patch,
            ])
        );
    }
}
