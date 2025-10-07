<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ResenaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResenaRepository::class)]
class Resena
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resenas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'resenas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comercio $comercio = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\Column]
    private ?bool $calificacion = null;

    #[ORM\Column(length: 1000)]
    private ?string $contenido = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $util = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $no_util = 0;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
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

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function isCalificacion(): ?bool
    {
        return $this->calificacion;
    }

    public function setCalificacion(bool $calificacion): static
    {
        $this->calificacion = $calificacion;

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

    public function getUtil(): ?int
    {
        return $this->util;
    }

    public function setUtil(int $util): static
    {
        $this->util = $util;

        return $this;
    }

    public function getNoUtil(): ?int
    {
        return $this->no_util;
    }

    public function setNoUtil(int $no_util): static
    {
        $this->no_util = $no_util;

        return $this;
    }
}
