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
    public function get(int $idp, string $idc): JsonResponse
    {
        $user = $this->getUser(); /** @var \App\Entity\User $user */
        
        // Encontrar comentario
        if ($idc === 'me') {
            $post = $this->postRepository->find($idp);
            if (!$user || !$post) {
                throw $this->createNotFoundException();
            }
            $comment = $this->commentRepository->findOneBy([
                'post' => $post,
                'user' => $user,
            ]);
        } else {
            $comment = $this->commentRepository->find((int)$idc);
        }
        if ($comment === null) throw $this->createNotFoundException();

        // Responder
        return $this->json([
            'data' => $comment,
        ], 200, [], ['groups' => ['comment:read']]);
    }
}
