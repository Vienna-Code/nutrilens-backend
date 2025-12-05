<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\Review;
use App\Entity\ReviewVote;
use App\Entity\User;
use App\Enum\Visibility;
use App\Repository\ReviewVoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReviewManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private ReviewVoteRepository $rvr,
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

    public function vote(Review $review, User $user, ?bool $newVote): bool
    {
        $reviewVote = $this->rvr->findOneBy([
            'user' => $user,
            'review' => $review,
        ]);

        if (!$reviewVote) {
            $reviewVote = new ReviewVote();
            $user->addReviewVote($reviewVote);
            $review->addReviewVote($reviewVote);
        }

        $oldVote = $reviewVote->isPositive();

        if ($newVote === $oldVote) {
            return false; // nothing changes
        }

        // Change vote
        $reviewVote->setPositive($newVote);
        $delta = match ([$oldVote, $newVote]) {
            [true, null]  => -1,
            [true, false] => -2,
            [null, true]  => +1,
            [null, false] => -1,
            [false, true] => +2,
            [false, null] => +1,
            default       => 0,
        };
        $review->setUseful($review->getUseful() + $delta);

        $this->em->persist($review);
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }
}