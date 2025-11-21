<?php

namespace App\Entity;

use App\Enum\ReportType;
use App\Repository\CommerceReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommerceReportRepository::class)]
class CommerceReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commerceReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commerce $commerce = null;

    #[ORM\ManyToOne(inversedBy: 'commerceReports')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: ReportType::class)]
    private ?ReportType $type = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

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

    public function setContent(string $content): static
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
}
