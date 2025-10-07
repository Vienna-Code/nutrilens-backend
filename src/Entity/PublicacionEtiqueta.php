<?php

namespace App\Entity;

use App\Repository\PublicacionEtiquetaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicacionEtiquetaRepository::class)]
class PublicacionEtiqueta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicacionEtiquetas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publicacion $publicacion = null;

    #[ORM\Column(length: 255)]
    private ?string $etiqueta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicacion(): ?Publicacion
    {
        return $this->publicacion;
    }

    public function setPublicacion(?Publicacion $publicacion): static
    {
        $this->publicacion = $publicacion;
        return $this;
    }

    public function getEtiqueta(): ?string
    {
        return $this->etiqueta;
    }

    public function setEtiqueta(string $etiqueta): static
    {
        $this->etiqueta = $etiqueta;
        return $this;
    }
}