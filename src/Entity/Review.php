<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use \App\Enum\Visibility;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Commerce $commerce = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?bool $positive = null;

    #[ORM\Column(length: 500)]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?int $useful = 0;

    #[ORM\Column(enumType: Visibility::class)]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?Visibility $visibility = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->visibility = Visibility::PUBLIC;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommerce(): ?Commerce
    {
        return $this->commerce;
    }

    public function setCommerce(?Commerce $commerce): static
    {
        $this->commerce = $commerce;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isPositive(): ?bool
    {
        return $this->positive;
    }

    public function setPositive(bool $positive): static
    {
        $this->positive = $positive;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getUseful(): ?int
    {
        return $this->useful;
    }

    public function setUseful(int $useful): static
    {
        $this->useful = $useful;

        return $this;
    }

    public function getVisibility(): ?Visibility
    {
        return $this->visibility;
    }

    public function setVisibility(Visibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }
}
