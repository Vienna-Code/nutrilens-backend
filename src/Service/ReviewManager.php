<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\Review;
use App\Entity\User;
use App\Enum\Visibility;
use Doctrine\ORM\EntityManagerInterface;

class ReviewManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
    ) {}

    public function create(array &$data, User &$user, Commerce &$commerce): Review
    {
        $review = new Review();
        $review->setPositive($data['positive']);
        $review->setContent($data['content']);

        $commerce->addReview($review);
        $user->addReview($review);

        $this->em->persist($commerce);
        $this->em->persist($user);
        $this->em->flush();

        return $review;
    }

    public function update(array &$data, Review &$review, User &$user): Review|false
    {
        // Solo admins o el que creó la review
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());
        if (!$isAdmin && $user !== $review->getUser()) {
            return false;
        }

        $review->setContent($data['content'] ?? $review->getContent());
        $review->setPositive($data['positive'] ?? $review->isPositive());
        $review->setUpdatedAt(new \DateTimeImmutable());
        if ($isAdmin) {
            if (isset($data['visibility'])) {
                $visibility = $data['visibility'] === 'private' ? Visibility::PRIVATE : Visibility::PUBLIC;
            } else {
                $visibility = $review->getVisibility();
            }
            $review->setVisibility($visibility);
        }

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }

    public function delete(Review $review): void
    {
        $this->em->remove($review);
        $this->em->flush();
    }
}