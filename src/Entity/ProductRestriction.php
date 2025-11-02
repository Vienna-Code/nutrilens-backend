<?php

namespace App\Entity;

use App\Enum\AlimentaryRestriction;
use App\Repository\ProductRestrictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRestrictionRepository::class)]
class ProductRestriction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'aptFor')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(enumType: AlimentaryRestriction::class)]
    private ?AlimentaryRestriction $restriction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getRestriction(): ?AlimentaryRestriction
    {
        return $this->restriction;
    }

    public function setRestriction(AlimentaryRestriction $restriction): static
    {
        $this->restriction = $restriction;

        return $this;
    }
}
