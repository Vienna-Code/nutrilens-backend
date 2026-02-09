<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use \App\Enum\Visibility;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['post:read', 'post:list'])]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['post:read', 'post:list'])]
    private ?int $views = 0;

    #[ORM\Column]
    #[Groups(['post:read', 'post:list'])]
    private ?int $upvotes = 0;

    #[ORM\Column(enumType: Visibility::class)]
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    private ?Visibility $visibility = null;

    /**
     * @var Collection<int, Tag>
     */
    #[Groups(['post:create', 'post:read', 'post:list', 'post:update'])]
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'posts', cascade: ['persist'])]
    private Collection $tags;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['persist'])]
    private Collection $comments;

    /**
     * @var Collection<int, PostVote>
     */
    #[ORM\OneToMany(targetEntity: PostVote::class, mappedBy: 'post', cascade: ['persist'], orphanRemoval: true)]
    private Collection $postVotes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->postVotes = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;

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

    public function getTags(): array
    {
        $atags = [];
        foreach ($this->tags as $atag) {
            $atags[] = $atag->getName();
        }
        return $atags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addPost($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removePost($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostVote>
     */
    public function getPostVotes(): Collection
    {
        return $this->postVotes;
    }

    public function addPostVote(PostVote $postVote): static
    {
        if (!$this->postVotes->contains($postVote)) {
            $this->postVotes->add($postVote);
            $postVote->setPost($this);
        }

        return $this;
    }

    public function removePostVote(PostVote $postVote): static
    {
        if ($this->postVotes->removeElement($postVote)) {
            // set the owning side to null (unless already changed)
            if ($postVote->getPost() === $this) {
                $postVote->setPost(null);
            }
        }

        return $this;
    }

    public function getUpvotes(): ?int
    {
        return $this->upvotes;
    }

    public function setUpvotes(int $upvotes): static
    {
        $this->upvotes = $upvotes;

        return $this;
    }

    #[Groups(['post:read', 'post:list'])]
    public function getTotalComments(): int
    {
        return $this->comments->count();
    }
}
