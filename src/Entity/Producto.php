<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\Condicion;
use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
#[ApiResource()]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comercio $comercio = null;

    #[ORM\Column(length: 80)]
    private ?string $nombre = null;

    #[ORM\Column(length: 80)]
    private ?string $marca = null;

    #[ORM\Column(length: 80)]
    private ?string $categoria = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $verificado = false;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Condicion::class)]
    private array $condicion = [];

    /**
     * @var Collection<int, ProductoImagen>
     */
    #[ORM\OneToMany(targetEntity: ProductoImagen::class, mappedBy: 'producto')]
    private Collection $productoImagenes;

    /**
     * @var Collection<int, ProductoReporte>
     */
    #[ORM\OneToMany(targetEntity: ProductoReporte::class, mappedBy: 'producto')]
    private Collection $productoReportes;

    public function __construct()
    {
        $this->productoImagenes = new ArrayCollection();
        $this->productoReportes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComercio(): ?Comercio
    {
        return $this->comercio;
    }

    public function setComercio(?Comercio $comercio): static
    {
        $this->comercio = $comercio;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function isVerificado(): ?bool
    {
        return $this->verificado;
    }

    public function setVerificado(bool $verificado): static
    {
        $this->verificado = $verificado;

        return $this;
    }

    /**
     * @return Condicion[]
     */
    public function getCondicion(): array
    {
        return $this->condicion;
    }

    public function setCondicion(array $condicion): static
    {
        $this->condicion = $condicion;

        return $this;
    }

    /**
     * @return Collection<int, ProductoImagen>
     */
    public function getProductoImagenes(): Collection
    {
        return $this->productoImagenes;
    }

    public function addProductoImagene(ProductoImagen $productoImagene): static
    {
        if (!$this->productoImagenes->contains($productoImagene)) {
            $this->productoImagenes->add($productoImagene);
            $productoImagene->setProducto($this);
        }

        return $this;
    }

    public function removeProductoImagene(ProductoImagen $productoImagene): static
    {
        if ($this->productoImagenes->removeElement($productoImagene)) {
            // set the owning side to null (unless already changed)
            if ($productoImagene->getProducto() === $this) {
                $productoImagene->setProducto(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductoReporte>
     */
    public function getProductoReportes(): Collection
    {
        return $this->productoReportes;
    }

    public function addProductoReporte(ProductoReporte $productoReporte): static
    {
        if (!$this->productoReportes->contains($productoReporte)) {
            $this->productoReportes->add($productoReporte);
            $productoReporte->setProducto($this);
        }

        return $this;
    }

    public function removeProductoReporte(ProductoReporte $productoReporte): static
    {
        if ($this->productoReportes->removeElement($productoReporte)) {
            // set the owning side to null (unless already changed)
            if ($productoReporte->getProducto() === $this) {
                $productoReporte->setProducto(null);
            }
        }

        return $this;
    }
}
