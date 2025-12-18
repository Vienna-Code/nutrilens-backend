<?php

namespace App\Entity;

use App\Enum\Visibility;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comment:create', 'comment:read', 'comment:list', 'comment:update'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[Groups(['comment:read', 'comment:list'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Post $post = null;

    #[ORM\Column(length: 500)]
    #[Groups(['comment:create', 'comment:read', 'comment:list', 'comment:update'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['comment:create', 'comment:read', 'comment:list', 'comment:update'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['comment:create', 'comment:read', 'comment:list', 'comment:update'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(enumType: Visibility::class)]
    #[Groups(['comment:create', 'comment:read', 'comment:list', 'comment:update'])]
    private ?Visibility $visibility = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'replying_to_id', referencedColumnName: 'id', nullable: true, onDelete: 'set null')]
    private ?self $replyingTo = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'replyingTo', cascade: ['persist'])]
    #[Groups(['comment:read', 'comment:list'])]
    private Collection $replies;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->visibility = Visibility::PUBLIC;
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

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

    public function getVisibility(): ?Visibility
    {
        return $this->visibility;
    }

    public function setVisibility(Visibility $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getReplyingTo(): ?self
    {
        return $this->replyingTo;
    }

    public function setReplyingTo(?self $replyingTo): static
    {
        $this->replyingTo = $replyingTo;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setReplyingTo($this);
        }

        return $this;
    }

    public function removeReply(self $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getReplyingTo() === $this) {
                $reply->setReplyingTo(null);
            }
        }

        return $this;
    }
}
