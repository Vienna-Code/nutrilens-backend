<?php

namespace App\Entity;

use App\Enum\ReportType;
use App\Repository\CommerceReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: CommerceReportRepository::class)]
class CommerceReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commerceReports')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?Commerce $commerce = null;

    #[ORM\ManyToOne(inversedBy: 'commerceReports')]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: ReportType::class)]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?ReportType $type = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['commercereport:create', 'commercereport:read', 'commercereport:list', 'commercereport:update'])]
    #[SerializedName('image')]
    private ?string $imagePath = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['commercereport:read', 'commercereport:list', 'commercereport:update'])]
    private ?bool $resolved = null;

    public function __construct() {
        $this->date = new \DateTimeImmutable(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommerce(): ?Commerce
    {
        return $this->commerce;
    }

    public function setCommerce(?Commerce $commerce): static
    {
        $this->commerce = $commerce;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): ?ReportType
    {
        return $this->type;
    }

    public function setType(ReportType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function isResolved(): ?bool
    {
        return $this->resolved;
    }

    public function setResolved(?bool $resolved): static
    {
        $this->resolved = $resolved;

        return $this;
    }
}
