<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PublicacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicacionRepository::class)]
#[ApiResource()]
class Publicacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?usuario $usuario = null;

    #[ORM\Column(length: 200)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenido = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\Column]
    private ?int $visitas = null;

    /**
     * @var Collection<int, PublicacionEtiqueta>
     */
    #[ORM\OneToMany(targetEntity: PublicacionEtiqueta::class, mappedBy: 'publicacion', orphanRemoval: true)]
    private Collection $publicacionEtiquetas;

    /**
     * @var Collection<int, Comentario>
     */
    #[ORM\OneToMany(targetEntity: Comentario::class, mappedBy: 'publicacion')]
    private Collection $comentarios;

    public function __construct()
    {
        $this->publicacionEtiquetas = new ArrayCollection();
        $this->comentarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getVisitas(): ?int
    {
        return $this->visitas;
    }

    public function setVisitas(int $visitas): static
    {
        $this->visitas = $visitas;

        return $this;
    }

    /**
     * @return Collection<int, PublicacionEtiqueta>
     */
    public function getPublicacionEtiquetas(): Collection
    {
        return $this->publicacionEtiquetas;
    }

    public function addPublicacionEtiqueta(PublicacionEtiqueta $publicacionEtiqueta): static
    {
        if (!$this->publicacionEtiquetas->contains($publicacionEtiqueta)) {
            $this->publicacionEtiquetas->add($publicacionEtiqueta);
            $publicacionEtiqueta->setPublicacion($this);
        }

        return $this;
    }

    public function removePublicacionEtiqueta(PublicacionEtiqueta $publicacionEtiqueta): static
    {
        if ($this->publicacionEtiquetas->removeElement($publicacionEtiqueta)) {
            // set the owning side to null (unless already changed)
            if ($publicacionEtiqueta->getPublicacion() === $this) {
                $publicacionEtiqueta->setPublicacion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comentario>
     */
    public function getComentarios(): Collection
    {
        return $this->comentarios;
    }

    public function addComentario(Comentario $comentario): static
    {
        if (!$this->comentarios->contains($comentario)) {
            $this->comentarios->add($comentario);
            $comentario->setPublicacion($this);
        }

        return $this;
    }

    public function removeComentario(Comentario $comentario): static
    {
        if ($this->comentarios->removeElement($comentario)) {
            // set the owning side to null (unless already changed)
            if ($comentario->getPublicacion() === $this) {
                $comentario->setPublicacion(null);
            }
        }

        return $this;
    }
}
