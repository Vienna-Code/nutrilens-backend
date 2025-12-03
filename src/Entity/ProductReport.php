<?php

namespace App\Entity;

use App\Enum\ReportType;
use App\Repository\ProductReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductReportRepository::class)]
class ProductReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productReports')]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(enumType: ReportType::class)]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?ReportType $type = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['productreport:create', 'productreport:list'])]
    private ?string $imagePath = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

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
}
