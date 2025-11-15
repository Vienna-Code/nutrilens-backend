<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commerce $commerce = null;

    #[ORM\Column(length: 50)]
    #[Groups(['product:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['product:list'])]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    #[Groups(['product:list'])]
    private ?string $category = null;

    #[ORM\Column]
    #[Groups(['product:list'])]
    private ?bool $verified = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:list'])]
    private ?string $imagePath = null;

    /**
     * @var Collection<int, ProductRestriction>
     */
    #[ORM\OneToMany(targetEntity: ProductRestriction::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $aptFor;

    /**
     * @var Collection<int, ProductReport>
     */
    #[ORM\OneToMany(targetEntity: ProductReport::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productReports;

    public function __construct()
    {
        $this->aptFor = new ArrayCollection();
        $this->productReports = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * @return Collection<int, ProductRestriction>
     */
    public function getAptFor(): Collection
    {
        return $this->aptFor;
    }

    public function addAptFor(ProductRestriction $aptFor): static
    {
        if (!$this->aptFor->contains($aptFor)) {
            $this->aptFor->add($aptFor);
            $aptFor->setProduct($this);
        }

        return $this;
    }

    public function removeAptFor(ProductRestriction $aptFor): static
    {
        if ($this->aptFor->removeElement($aptFor)) {
            // set the owning side to null (unless already changed)
            if ($aptFor->getProduct() === $this) {
                $aptFor->setProduct(null);
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
            $productReport->setProduct($this);
        }

        return $this;
    }

    public function removeProductReport(ProductReport $productReport): static
    {
        if ($this->productReports->removeElement($productReport)) {
            // set the owning side to null (unless already changed)
            if ($productReport->getProduct() === $this) {
                $productReport->setProduct(null);
            }
        }

        return $this;
    }

    #[Groups(['product:list'])]
    #[SerializedName('aptFor')]
    public function getAptForValues(): array
    {
        return $this->aptFor
            ->map(fn(ProductRestriction $r) => $r->getRestriction()->value)
            ->toArray();
    }
}
