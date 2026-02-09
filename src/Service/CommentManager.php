<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostVote;
use App\Entity\User;
use App\Enum\AlimentaryRestriction;
use App\Enum\UserRank;
use App\Enum\Visibility;
use App\Repository\PostVoteRepository;
use App\Repository\TagRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CommentManager
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function create(array &$data, User $user, Post $post, ?Comment $parent = null): Comment
    {
        $comment = new Comment();
        $comment->setContent($data['content']);

        if ($parent) {
            if ($parent->getReplyingTo() !== null) {
                $comment->setTaggingUser($parent->getUser());
                $parent = $parent->getReplyingTo();
            }

            $parent->addReply($comment);
            $this->em->persist($parent);
        }
        $user->addComment($comment);
        $post->addComment($comment);

        $this->em->persist($user);
        $this->em->persist($post);
        $this->em->flush();

        return $comment;
    }

    public function update(array &$data, Comment $comment, User $user): Comment|false
    {
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());

        $comment->setContent($data['content'] ?? $comment->getContent());
        $comment->setUpdatedAt(new DateTimeImmutable('now'));
        if ($isAdmin) {
            if ($data['visibility']) {
                $comment->setVisibility(Visibility::tryFrom($data['visibility']));
            }
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $this->em->remove($comment);
        $this->em->flush();
    }
}