<?php

namespace App\Entity;

use App\Repository\PostVoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostVoteRepository::class)]
class PostVote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $positive = null;

    #[ORM\ManyToOne(inversedBy: 'postVotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;

    #[ORM\ManyToOne(inversedBy: 'postVotes')]
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

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
