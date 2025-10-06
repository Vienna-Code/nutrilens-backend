<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ComercioImagenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComercioImagenRepository::class)]
#[ApiResource()]
class ComercioImagen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comercioImagenes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comercio $comercio = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta = null;

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

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): static
    {
        $this->ruta = $ruta;

        return $this;
    }
}
