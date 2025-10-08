<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata;
use App\Repository\ComentarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComentarioRepository::class)]
#[ApiResource(
    operations: [
        new Metadata\GetCollection(),
        new Metadata\Post(),
        new Metadata\Patch(),
        new Metadata\Delete()
    ],
    normalizationContext: ['groups' => ['comentario:read']],
    denormalizationContext: ['groups' => ['comentario:write']]
)]
class Comentario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comentario:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comentario:read', 'comentario:write'])]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'comentarios')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comentario:read', 'comentario:write'])]
    private ?Publicacion $publicacion = null;

    #[ORM\Column(length: 500)]
    #[Groups(['comentario:read', 'comentario:write'])]
    private ?string $contenido = null;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['comentario:read'])]
    private ?\DateTimeImmutable $fecha = null;

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

    public function getPublicacion(): ?Publicacion
    {
        return $this->publicacion;
    }

    public function setPublicacion(?Publicacion $publicacion): static
    {
        $this->publicacion = $publicacion;

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
}
