<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata;
use App\Repository\ComercioReporteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComercioReporteRepository::class)]
#[ApiResource(
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(),
        new Metadata\Patch(),
        new Metadata\Delete()
    ],
    normalizationContext: ['groups' => ['creporte:read']],
    denormalizationContext: ['groups' => ['creporte:write']]
)]
class ComercioReporte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['creporte:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'comercioReportes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['creporte:read', 'creporte:write'])]
    private ?Comercio $comercio = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['creporte:read'])]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['creporte:read', 'creporte:write'])]
    private ?string $contenido = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['creporte:read', 'creporte:write'])]
    private ?Usuario $usuario = null;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
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

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

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

    public function getUsuario(): ?usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }
}
