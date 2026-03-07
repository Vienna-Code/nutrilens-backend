<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Enum\Visibility;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[Groups(['review:list:me'])]
    private ?Commerce $commerce = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?bool $positive = null;

    #[ORM\Column(length: 500)]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?int $useful = 0;

    #[ORM\Column(enumType: Visibility::class)]
    #[Groups(['review:create', 'review:read', 'review:list', 'review:update',
                      'commerce:read'])]
    private ?Visibility $visibility = null;

    /**
     * @var Collection<int, ReviewVote>
     */
    #[ORM\OneToMany(targetEntity: ReviewVote::class, mappedBy: 'review', cascade: ['persist'], orphanRemoval: true)]
    private Collection $reviewVotes;

    #[ORM\Column(nullable: true)]
    private ?bool $passThreshold = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->visibility = Visibility::PUBLIC;
        $this->reviewVotes = new ArrayCollection();
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

    /**
     * @return Collection<int, ReviewVote>
     */
    public function getReviewVote(): Collection
    {
        return $this->reviewVotes;
    }

    public function addReviewVote(ReviewVote $reviewVote): static
    {
        if (!$this->reviewVotes->contains($reviewVote)) {
            $this->reviewVotes->add($reviewVote);
            $reviewVote->setReview($this);
        }

        return $this;
    }

    public function removeReviewVote(ReviewVote $reviewVote): static
    {
        if ($this->reviewVotes->removeElement($reviewVote)) {
            // set the owning side to null (unless already changed)
            if ($reviewVote->getReview() === $this) {
                $reviewVote->setReview(null);
            }
        }

        return $this;
    }

    public function isPassThreshold(): ?bool
    {
        return $this->passThreshold;
    }

    public function setPassThreshold(?bool $passThreshold): static
    {
        $this->passThreshold = $passThreshold;

        return $this;
    }
}
