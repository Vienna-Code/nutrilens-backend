<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\PostVote;
use App\Entity\User;
use App\Enum\Visibility;
use App\Repository\ImageRepository;
use App\Repository\PostVoteRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class PostManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private TagRepository $tagRepository,
        private ImageRepository $imageRepository,

        private PostVoteRepository $pvr,
    ) {}
    
    public function create(array &$data, User &$user): Post|string
    {
        $post = new Post();
        $post->setTitle($data['title']);
        $post->setContent($data['content']);
        $post->setVisibility(Visibility::tryFrom($data['visibility'] ?? 'public'));
        foreach ($data['tags'] as $tag) {
            $realTag = $this->tagRepository->findOneBy([
                'name' => $tag
            ]);
            if (!$realTag) {
                return $tag;
            }
            $post->addTag($realTag);
        }

        // Imágenes
        $uuids = $data['attachments'] ?? [];
        if (!empty($uuids)) {
            $uuidObjects = array_map(
                fn (string $uuid) => Uuid::fromString($uuid),
                $uuids
            );
            $images = $this->imageRepository->findBy([
                'id' => $uuidObjects,
            ]);
            if (\count($images) !== \count($uuids)) {
                throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
            }
            foreach ($images as $image) {
                if ($image->getUser() !== $user && \in_array('ROLE_ADMIN', $user->getRoles())) {
                    throw new \InvalidArgumentException('Imagen de ID ' . $image->getId()->toRfc4122() . ' no fue subida por el usuario.');
                }
            }
            $post->setAttachments($data['attachments']);
        }

        $user->addPost($post);

        $this->em->persist($user);
        $this->em->flush();

        return $post;
    }

    public function update(array &$data, Post &$post, User &$user): Post|string
    {
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());

        $post->setContent($data['content'] ?? $post->getContent());
        $post->setUpdatedAt(new \DateTimeImmutable('now'));
        if (isset($data['visibility'])) {
            $post->setVisibility(Visibility::tryFrom($data['visibility']));
        }
        if (isset($data['tags'])) {
            foreach ($post->getTags() as $tag) {
                $post->removeTag($this->tagRepository->findOneBy(['name' => $tag]));
            }
            foreach ($data['tags'] as $tag) {
                $realTag = $this->tagRepository->findOneBy([
                    'name' => $tag
                ]);
                if (!$realTag) {
                    return $tag;
                }
                $post->addTag($realTag);
            }
        }

        // Imágenes
        if (isset($data['attachments'])) {
            $uuids = $data['attachments'] ?? [];
            
            if (!empty($uuids)) {
                $uuidObjects = array_map(
                    fn (string $uuid) => Uuid::fromString($uuid),
                    $uuids
                );

                $images = $this->imageRepository->findBy([
                    'id' => $uuidObjects,
                ]);

                if (\count($images) !== \count($uuids)) {
                    throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
                }

                foreach ($images as $image) {
                    if ($image->getUser() !== $user && !\in_array('ROLE_ADMIN', $user->getRoles())) {
                        throw new \InvalidArgumentException(
                            'Imagen de ID ' . $image->getId()->toRfc4122() . ' no fue subida por el usuario.'
                        );
                    }
                }

                $post->setAttachments($data['attachments']);
            } else {
                $post->setAttachments([]);
            }
        }

        // Funciones de admin
        if ($isAdmin) {
            $post->setTitle($data['title'] ?? $post->getTitle());
        }

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function delete(Post &$post): void
    {
        $this->em->remove($post);
        $this->em->flush();
    }

    public function vote(Post $post, User $user, ?bool $newVote): bool
    {
        $postVote = $this->pvr->findOneBy([
            'user' => $user,
            'post' => $post,
        ]);

        if (!$postVote) {
            $postVote = new PostVote();
            $user->addPostVote($postVote);
            $post->addPostVote($postVote);
        }

        $oldVote = $postVote->isPositive();

        if ($newVote === $oldVote) {
            return false; // No cambia nada
        }

        // Cambiar voto
        $postVote->setPositive($newVote);
        $delta = match ([$oldVote, $newVote]) {
            [true, null]  => -1,
            [true, false] => -2,
            [null, true]  => +1,
            [null, false] => -1,
            [false, true] => +2,
            [false, null] => +1,
            default       => 0,
        };
        $post->setUpvotes($post->getUpvotes() + $delta);

        $this->gm->rewardPostPoints($post);

        $this->em->persist($post);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }

    public function addView(Post &$post): void
    {
        // TODO: quizas cambiar esto ya que es muy rudimentario / hacer F5 le agrega visitas
        $post->setViews($post->getViews() + 1);
        $this->em->persist($post);
        $this->em->flush();
    }
}