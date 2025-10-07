<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata;
use App\Repository\ProductoReporteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductoReporteRepository::class)]
#[ApiResource(
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(),
        new Metadata\Patch(),
        new Metadata\Delete()
    ],
    normalizationContext: ['groups' => ['preporte:read']],
    denormalizationContext: ['groups' => ['preporte:write']]
)]
class ProductoReporte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preporte:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productoReportes')]
    #[ORM\JoinColumn(nullable: false)]
     #[Groups(['preporte:read', 'preporte:write'])]
    private ?Producto $producto = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['preporte:read'])]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\Column(length: 1000)]
     #[Groups(['preporte:read', 'preporte:write'])]
    private ?string $contenido = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
     #[Groups(['preporte:read', 'preporte:write'])]
    private ?Usuario $usuario = null;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): static
    {
        $this->producto = $producto;

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

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }
}
