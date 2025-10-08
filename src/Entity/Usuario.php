<?php

namespace App\Entity;

use ApiPlatform\Metadata;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\Rol;
use App\Enum\Condicion;
use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ApiResource(
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(),
        new Metadata\Patch()
    ],
    normalizationContext: ['groups' => ['usuario:read']],
    denormalizationContext: ['groups' => ['usuario:write']]
)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['usuario:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    #[Groups(['usuario:read', 'usuario:write'])]
    private ?string $nombre_usuario = null;

    #[ORM\Column(length: 320, unique: true)]
    #[Groups(['usuario:read', 'usuario:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $verificacion_email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['usuario:write'])]
    private ?string $contrasena = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Condicion::class)]
    #[Groups(['usuario:read', 'usuario:write'])]
    private array $condicion = [];

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['usuario:read'])]
    private ?\DateTimeImmutable $fecha_registro;

    #[ORM\Column(enumType: Rol::class, options: ['default' => 'no_verificado'])]
    #[Groups(['usuario:read'])]
    private ?Rol $rol = Rol::NO_VERIFICADO;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['usuario:read', 'usuario:write'])]
    private ?string $foto_perfil = null;

    /**
     * @var Collection<int, Gamificacion>
     */
    #[ORM\OneToMany(targetEntity: Gamificacion::class, mappedBy: 'usuario')]
    private Collection $gamificaciones;

    /**
     * @var Collection<int, Publicacion>
     */
    #[ORM\OneToMany(targetEntity: Publicacion::class, mappedBy: 'usuario')]
    private Collection $publicaciones;

    /**
     * @var Collection<int, Resena>
     */
    #[ORM\OneToMany(targetEntity: Resena::class, mappedBy: 'usuario')]
    private Collection $resenas;

    public function __construct()
    {
        $this->gamificaciones = new ArrayCollection();
        $this->publicaciones = new ArrayCollection();
        $this->resenas = new ArrayCollection();
        $this->fecha_registro = new \DateTimeImmutable();
        $this->rol = Rol::NO_VERIFICADO;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombre_usuario;
    }

    public function setNombreUsuario(string $nombre_usuario): static
    {
        $this->nombre_usuario = $nombre_usuario;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getVerificacionEmail(): ?string
    {
        return $this->verificacion_email;
    }

    public function setVerificacionEmail(string $verificacion_email): static
    {
        $this->verificacion_email = $verificacion_email;

        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->contrasena = $contrasena;

        return $this;
    }

    /**
     * @return Condicion[]
     */
    public function getCondicion(): array
    {
        return $this->condicion;
    }

    public function setCondicion(array $condicion): static
    {
        $this->condicion = $condicion;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeImmutable
    {
        return $this->fecha_registro;
    }

    public function setFechaRegistro(\DateTimeImmutable $fecha_registro): static
    {
        $this->fecha_registro = $fecha_registro;

        return $this;
    }

    public function getRol(): ?Rol
    {
        return $this->rol;
    }

    public function setRol(Rol $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    public function getFotoPerfil(): ?string
    {
        return $this->foto_perfil;
    }

    public function setFotoPerfil(?string $foto_perfil): static
    {
        $this->foto_perfil = $foto_perfil;

        return $this;
    }

    /**
     * @return Collection<int, Gamificacion>
     */
    public function getGamificaciones(): Collection
    {
        return $this->gamificaciones;
    }

    public function addGamificacione(Gamificacion $gamificacione): static
    {
        if (!$this->gamificaciones->contains($gamificacione)) {
            $this->gamificaciones->add($gamificacione);
            $gamificacione->setUsuario($this);
        }

        return $this;
    }

    public function removeGamificacione(Gamificacion $gamificacione): static
    {
        if ($this->gamificaciones->removeElement($gamificacione)) {
            // set the owning side to null (unless already changed)
            if ($gamificacione->getUsuario() === $this) {
                $gamificacione->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Publicacion>
     */
    public function getPublicaciones(): Collection
    {
        return $this->publicaciones;
    }

    public function addPublicacione(Publicacion $publicacione): static
    {
        if (!$this->publicaciones->contains($publicacione)) {
            $this->publicaciones->add($publicacione);
            $publicacione->setUsuario($this);
        }

        return $this;
    }

    public function removePublicacione(Publicacion $publicacione): static
    {
        if ($this->publicaciones->removeElement($publicacione)) {
            // set the owning side to null (unless already changed)
            if ($publicacione->getUsuario() === $this) {
                $publicacione->setUsuario(null);
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
            $resena->setUsuario($this);
        }

        return $this;
    }

    public function removeResena(Resena $resena): static
    {
        if ($this->resenas->removeElement($resena)) {
            // set the owning side to null (unless already changed)
            if ($resena->getUsuario() === $this) {
                $resena->setUsuario(null);
            }
        }

        return $this;
    }

    #[Groups(['usuario:read'])]
    public function getPuntos(): int
    {
        $puntos = 0;
        foreach ($this->gamificaciones as $g) {
            $puntos += $g->getPuntos();
        }

        return $puntos;
    }
}
