<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ComercioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComercioRepository::class)]
#[ApiResource()]
class Comercio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50)]
    private ?string $tipo = null;

    #[ORM\Column(length: 255)]
    private ?string $coordenadas = null;

    #[ORM\Column(length: 255)]
    private ?string $horarios = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $foto_fachada = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metodos_pago = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contacto = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $verificado = false;

    /**
     * @var Collection<int, Producto>
     */
    #[ORM\OneToMany(targetEntity: Producto::class, mappedBy: 'comercio')]
    private Collection $productos;

    /**
     * @var Collection<int, ComercioImagen>
     */
    #[ORM\OneToMany(targetEntity: ComercioImagen::class, mappedBy: 'comercio')]
    private Collection $comercioImagenes;

    /**
     * @var Collection<int, Resena>
     */
    #[ORM\OneToMany(targetEntity: Resena::class, mappedBy: 'comercio')]
    private Collection $resenas;

    /**
     * @var Collection<int, ComercioReporte>
     */
    #[ORM\OneToMany(targetEntity: ComercioReporte::class, mappedBy: 'comercio')]
    private Collection $comercioReportes;

    public function __construct()
    {
        $this->productos = new ArrayCollection();
        $this->comercioImagenes = new ArrayCollection();
        $this->resenas = new ArrayCollection();
        $this->comercioReportes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getCoordenadas(): ?string
    {
        return $this->coordenadas;
    }

    public function setCoordenadas(string $coordenadas): static
    {
        $this->coordenadas = $coordenadas;

        return $this;
    }

    public function getHorarios(): ?string
    {
        return $this->horarios;
    }

    public function setHorarios(string $horarios): static
    {
        $this->horarios = $horarios;

        return $this;
    }

    public function getFotoFachada(): ?string
    {
        return $this->foto_fachada;
    }

    public function setFotoFachada(?string $foto_fachada): static
    {
        $this->foto_fachada = $foto_fachada;

        return $this;
    }

    public function getMetodosPago(): ?string
    {
        return $this->metodos_pago;
    }

    public function setMetodosPago(?string $metodos_pago): static
    {
        $this->metodos_pago = $metodos_pago;

        return $this;
    }

    public function getContacto(): ?string
    {
        return $this->contacto;
    }

    public function setContacto(?string $contacto): static
    {
        $this->contacto = $contacto;

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
     * @return Collection<int, Producto>
     */
    public function getProductos(): Collection
    {
        return $this->productos;
    }

    public function addProducto(Producto $producto): static
    {
        if (!$this->productos->contains($producto)) {
            $this->productos->add($producto);
            $producto->setComercio($this);
        }

        return $this;
    }

    public function removeProducto(Producto $producto): static
    {
        if ($this->productos->removeElement($producto)) {
            // set the owning side to null (unless already changed)
            if ($producto->getComercio() === $this) {
                $producto->setComercio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ComercioImagen>
     */
    public function getComercioImagenes(): Collection
    {
        return $this->comercioImagenes;
    }

    public function addComercioImagene(ComercioImagen $comercioImagene): static
    {
        if (!$this->comercioImagenes->contains($comercioImagene)) {
            $this->comercioImagenes->add($comercioImagene);
            $comercioImagene->setComercio($this);
        }

        return $this;
    }

    public function removeComercioImagene(ComercioImagen $comercioImagene): static
    {
        if ($this->comercioImagenes->removeElement($comercioImagene)) {
            // set the owning side to null (unless already changed)
            if ($comercioImagene->getComercio() === $this) {
                $comercioImagene->setComercio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Resena>
     */
    public function getResenas(): Collection
    {
        return $this->resenas;
    }

    public function addResena(Resena $resena): static
    {
        if (!$this->resenas->contains($resena)) {
            $this->resenas->add($resena);
            $resena->setComercio($this);
        }

        return $this;
    }

    public function removeResena(Resena $resena): static
    {
        if ($this->resenas->removeElement($resena)) {
            // set the owning side to null (unless already changed)
            if ($resena->getComercio() === $this) {
                $resena->setComercio(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ComercioReporte>
     */
    public function getComercioReportes(): Collection
    {
        return $this->comercioReportes;
    }

    public function addComercioReporte(ComercioReporte $comercioReporte): static
    {
        if (!$this->comercioReportes->contains($comercioReporte)) {
            $this->comercioReportes->add($comercioReporte);
            $comercioReporte->setComercio($this);
        }

        return $this;
    }

    public function removeComercioReporte(ComercioReporte $comercioReporte): static
    {
        if ($this->comercioReportes->removeElement($comercioReporte)) {
            // set the owning side to null (unless already changed)
            if ($comercioReporte->getComercio() === $this) {
                $comercioReporte->setComercio(null);
            }
        }

        return $this;
    }
}
