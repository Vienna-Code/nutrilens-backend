<?php

namespace App\Entity;

use App\Enum\AlimentaryRestriction;
use App\Enum\UserRank;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'username_unique_idx', columns: ['username'])]
#[ORM\UniqueConstraint(name: 'email_unique_idx', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:create', 'user:read', 'user:update',
                      'review:create', 'review:read', 'review:list', 'review:update',
                      'commercereport:create', 'commercereport:list',
                      'productreport:create', 'productreport:list',
                      'post:read', 'post:list',
                      'comment:read', 'comment:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    #[Groups(['user:create', 'user:read', 'user:update',
                      'review:create', 'review:read', 'review:list', 'review:update',
                      'commercereport:create', 'commercereport:list',
                      'productreport:create', 'productreport:list',
                      'post:read', 'post:list',
                      'comment:read', 'comment:list'])]
    private ?string $username = null;

    #[ORM\Column(length: 320)]
    #[Groups(['user:create', 'user:update'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $verification = null;

    #[ORM\Column]
    #[Groups(['user:create', 'user:read', 'user:update'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['user:create', 'user:read', 'user:update',
                      'review:create', 'review:read', 'review:list', 'review:update',
                      'commercereport:create', 'commercereport:list',
                      'productreport:create', 'productreport:list',
                      'post:read', 'post:list',
                      'comment:read', 'comment:list'])]
    private ?UserRank $userRank = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:create', 'user:read', 'user:update'])]
    private array $roles = [];

    #[ORM\Column(type: Types::JSON, enumType: AlimentaryRestriction::class)]
    #[Groups(['user:create', 'user:read', 'user:update'])]
    private array $alimentaryRestrictions = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:create', 'user:read', 'user:update',
                      'review:create', 'review:read', 'review:list', 'review:update',
                      'commercereport:create', 'commercereport:list',
                      'productreport:create', 'productreport:list',
                      'post:read', 'post:list',
                      'comment:read', 'comment:list'])]
    private ?string $profilePicture = null;

    #[ORM\Column]
    #[Groups(['user:create', 'user:read', 'user:update',
                      'review:create', 'review:read', 'review:list', 'review:update',
                      'commercereport:create', 'commercereport:list',
                      'productreport:create', 'productreport:list',
                      'post:read', 'post:list',
                      'comment:read', 'comment:list'])]
    private ?int $points = null;

    /**
     * @var Collection<int, UserGamification>
     */
    #[ORM\OneToMany(targetEntity: UserGamification::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    private Collection $userGamifications;

    /**
     * @var Collection<int, CommerceReport>
     */
    #[ORM\OneToMany(targetEntity: CommerceReport::class, mappedBy: 'user')]
    private Collection $commerceReports;

    /**
     * @var Collection<int, ProductReport>
     */
    #[ORM\OneToMany(targetEntity: ProductReport::class, mappedBy: 'user')]
    private Collection $productReports;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $reviews;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $posts;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $comments;

    /**
     * @var Collection<int, ReviewVote>
     */
    #[ORM\OneToMany(targetEntity: ReviewVote::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    private Collection $reviewVotes;

    /**
     * @var Collection<int, PostVote>
     */
    #[ORM\OneToMany(targetEntity: PostVote::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    private Collection $postVotes;

    public function __construct()
    {
        $this->userGamifications = new ArrayCollection();
        $this->commerceReports = new ArrayCollection();
        $this->productReports = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reviewVotes = new ArrayCollection();
        $this->postVotes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getVerification(): ?string
    {
        return $this->verification;
    }

    public function setVerification(?string $verification): static
    {
        $this->verification = $verification;

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

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return AlimentaryRestriction[]
     */
    public function getAlimentaryRestrictions(): array
    {
        return $this->alimentaryRestrictions;
    }

    public function setAlimentaryRestrictions(array $alimentaryRestrictions): static
    {
        $this->alimentaryRestrictions = $alimentaryRestrictions;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getUserRank(): ?UserRank
    {
        return match (true) {
            $this->points < 100   => UserRank::BRONZE,
            $this->points < 400   => UserRank::SILVER,
            $this->points < 1000  => UserRank::GOLD,
            default               => UserRank::PLATINUM,
        };
    }

    /**
     * @return Collection<int, UserGamification>
     */
    public function getUserGamifications(): Collection
    {
        return $this->userGamifications;
    }

    public function addUserGamification(UserGamification $userGamification): static
    {
        if (!$this->userGamifications->contains($userGamification)) {
            $this->userGamifications->add($userGamification);
            $userGamification->setUser($this);
            $this->points += $userGamification->getPoints();
        }

        return $this;
    }

    public function removeUserGamification(UserGamification $userGamification): static
    {
        if ($this->userGamifications->removeElement($userGamification)) {
            // set the owning side to null (unless already changed)
            if ($userGamification->getUser() === $this) {
                $userGamification->setUser(null);
            }
            $this->points -= $userGamification->getPoints();
        }

        return $this;
    }

    /**
     * @return Collection<int, CommerceReport>
     */
    public function getCommerceReports(): Collection
    {
        return $this->commerceReports;
    }

    public function addCommerceReport(CommerceReport $commerceReport): static
    {
        if (!$this->commerceReports->contains($commerceReport)) {
            $this->commerceReports->add($commerceReport);
            $commerceReport->setUser($this);
        }

        return $this;
    }

    public function removeCommerceReport(CommerceReport $commerceReport): static
    {
        if ($this->commerceReports->removeElement($commerceReport)) {
            // set the owning side to null (unless already changed)
            if ($commerceReport->getUser() === $this) {
                $commerceReport->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductReport>
     */
    public function getProductReports(): Collection
    {
        return $this->productReports;
    }

    public function addProductReport(ProductReport $productReport): static
    {
        if (!$this->productReports->contains($productReport)) {
            $this->productReports->add($productReport);
            $productReport->setUser($this);
        }

        return $this;
    }

    public function removeProductReport(ProductReport $productReport): static
    {
        if ($this->productReports->removeElement($productReport)) {
            // set the owning side to null (unless already changed)
            if ($productReport->getUser() === $this) {
                $productReport->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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
            $reviewVote->setUser($this);
        }

        return $this;
    }

    public function removeReviewVote(ReviewVote $reviewVote): static
    {
        if ($this->reviewVotes->removeElement($reviewVote)) {
            // set the owning side to null (unless already changed)
            if ($reviewVote->getUser() === $this) {
                $reviewVote->setUser(null);
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
            $postVote->setUser($this);
        }

        return $this;
    }

    public function removePostVote(PostVote $postVote): static
    {
        if ($this->postVotes->removeElement($postVote)) {
            // set the owning side to null (unless already changed)
            if ($postVote->getUser() === $this) {
                $postVote->setUser(null);
            }
        }

        return $this;
    }
}
