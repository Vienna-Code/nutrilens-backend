<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProductoImagenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoImagenRepository::class)]
#[ApiResource()]
class ProductoImagen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productoImagenes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?producto $producto = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducto(): ?producto
    {
        return $this->producto;
    }

    public function setProducto(?producto $producto): static
    {
        $this->producto = $producto;

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
