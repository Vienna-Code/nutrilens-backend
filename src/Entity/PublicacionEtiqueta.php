<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PublicacionEtiquetaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicacionEtiquetaRepository::class)]
#[ApiResource()]
class PublicacionEtiqueta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'publicacionEtiquetas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?publicacion $publicacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicacion(): ?publicacion
    {
        return $this->publicacion;
    }

    public function setPublicacion(?publicacion $publicacion): static
    {
        $this->publicacion = $publicacion;

        return $this;
    }
}
