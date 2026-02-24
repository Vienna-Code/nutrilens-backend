<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['image:read', 'image:create'])]
    private Uuid $id;

    #[ORM\Column]
    #[Groups(['image:read', 'image:create'])]
    private \DateTimeImmutable $date;

    #[ORM\Column(length: 100)]
    #[Groups(['image:read', 'image:create'])]
    private ?string $mimeType = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[Groups(['image:read', 'image:create'])]
    #[SerializedName('uploadedBy')]
    private ?User $user = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}