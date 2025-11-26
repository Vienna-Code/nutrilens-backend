<?php

namespace App\Entity;

use App\Enum\CommerceType;
use App\Repository\CommerceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommerceRepository::class)]
class Commerce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?CommerceType $type = null;

    #[ORM\Column]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?float $coordsLat = null;

    #[ORM\Column]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?float $coordsLon = null;

    #[ORM\Column(length: 255)]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?bool $verified = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?array $contactInfo = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private ?array $paymentMethods = null;

    /**
     * @var Collection<int, CommerceImage>
     */
    #[ORM\OneToMany(targetEntity: CommerceImage::class, mappedBy: 'commerce', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['commerce:read'])]
    private Collection $commerceImages;

    /**
     * @var Collection<int, CommerceSchedule>
     */
    #[ORM\OneToMany(targetEntity: CommerceSchedule::class, mappedBy: 'commerce', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['commerce:create', 'commerce:read', 'commerce:list', 'commerce:update'])]
    private Collection $commerceSchedules;

    /**
     * @var Collection<int, CommerceReport>
     */
    #[ORM\OneToMany(targetEntity: CommerceReport::class, mappedBy: 'commerce', orphanRemoval: true, cascade: ['persist'])]
    private Collection $commerceReports;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'commerce', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['commerce:read'])]
    private Collection $products;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'commerce', cascade: ['persist'])]
    private Collection $reviews;

    #[ORM\Column]
    #[Groups(['commerce:read', 'commerce:list'])]
    private ?int $totalReviews = 0;

    #[ORM\Column]
    #[Groups(['commerce:read', 'commerce:list'])]
    private ?int $positiveReviews = 0;

    public function __construct()
    {
        $this->commerceImages = new ArrayCollection();
        $this->commerceSchedules = new ArrayCollection();
        $this->commerceReports = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?CommerceType
    {
        return $this->type;
    }

    public function setType(CommerceType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCoordsLat(): ?float
    {
        return $this->coordsLat;
    }

    public function setCoordsLat(float $coordsLat): static
    {
        $this->coordsLat = $coordsLat;

        return $this;
    }

    public function getCoordsLon(): ?float
    {
        return $this->coordsLon;
    }

    public function setCoordsLon(float $coordsLon): static
    {
        $this->coordsLon = $coordsLon;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getContactInfo(): array
    {
        return $this->contactInfo;
    }

    public function setContactInfo(?array $contactInfo): static
    {
        $this->contactInfo = $contactInfo;

        return $this;
    }

    public function getPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(?array $paymentMethods): static
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }

    /**
     * @return Collection<int, CommerceImage>
     */
    public function getCommerceImages(): Collection
    {
        return $this->commerceImages;
    }

    public function addCommerceImage(CommerceImage $commerceImage): static
    {
        if (!$this->commerceImages->contains($commerceImage)) {
            $this->commerceImages->add($commerceImage);
            $commerceImage->setCommerce($this);
        }

        return $this;
    }

    public function removeCommerceImage(CommerceImage $commerceImage): static
    {
        if ($this->commerceImages->removeElement($commerceImage)) {
            // set the owning side to null (unless already changed)
            if ($commerceImage->getCommerce() === $this) {
                $commerceImage->setCommerce(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommerceSchedule>
     */
    public function getCommerceSchedules(): Collection
    {
        return $this->commerceSchedules;
    }

    public function addCommerceSchedule(CommerceSchedule $commerceSchedule): static
    {
        if (!$this->commerceSchedules->contains($commerceSchedule)) {
            $this->commerceSchedules->add($commerceSchedule);
            $commerceSchedule->setCommerce($this);
        }

        return $this;
    }

    public function removeCommerceSchedule(CommerceSchedule $commerceSchedule): static
    {
        if ($this->commerceSchedules->removeElement($commerceSchedule)) {
            // set the owning side to null (unless already changed)
            if ($commerceSchedule->getCommerce() === $this) {
                $commerceSchedule->setCommerce(null);
            }
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
            $commerceReport->setCommerce($this);
        }

        return $this;
    }

    public function removeCommerceReport(CommerceReport $commerceReport): static
    {
        if ($this->commerceReports->removeElement($commerceReport)) {
            // set the owning side to null (unless already changed)
            if ($commerceReport->getCommerce() === $this) {
                $commerceReport->setCommerce(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCommerce($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCommerce() === $this) {
                $product->setCommerce(null);
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
            $review->setCommerce($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getCommerce() === $this) {
                $review->setCommerce(null);
            }
        }

        return $this;
    }

    public function getTotalReviews(): ?int
    {
        return $this->totalReviews;
    }

    public function setTotalReviews(int $totalReviews): static
    {
        $this->totalReviews = $totalReviews;

        return $this;
    }

    public function getPositiveReviews(): ?int
    {
        return $this->positiveReviews;
    }

    public function setPositiveReviews(int $positiveReviews): static
    {
        $this->positiveReviews = $positiveReviews;

        return $this;
    }
}
