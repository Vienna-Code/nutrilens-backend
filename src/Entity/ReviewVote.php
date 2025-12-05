<?php

namespace App\Entity;

use App\Repository\ReviewVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewVoteRepository::class)]
class ReviewVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $positive = null;

    #[ORM\ManyToOne(inversedBy: 'reviewVotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Review $review = null;

    #[ORM\ManyToOne(inversedBy: 'reviewVotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isPositive(): ?bool
    {
        return $this->positive;
    }

    public function setPositive(?bool $positive): static
    {
        $this->positive = $positive;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
