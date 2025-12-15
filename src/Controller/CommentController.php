<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
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

final class CommentController extends AbstractController
{
    public function __construct(
        private ValidationService $validation,
        private LoggerInterface $logger,

        private CommentRepository $commentRepository,

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
}
