<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductReport;
use App\Entity\User;
use App\Enum\ReportType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductReportManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, Product &$product, User &$user): ProductReport
    {
        $productReport = new ProductReport();
        $productReport->setContent($data['content'] ?? null);
        $productReport->setType(ReportType::tryFrom($data['type']));
        if (isset($data['image']) && $data['image'] != null) {
            if (!$this->imageRepository->find($data['image'])) {
                throw new \InvalidArgumentException('La imagen no fue encontrada');
            }
            $productReport->setImagePath($data['image']);
        }

        $product->addProductReport($productReport);
        $user->addProductReport($productReport);

        $this->em->persist($product);
        $this->em->persist($user);
        $this->em->flush();

        return $productReport;
    }

    public function update(array &$data, ProductReport $report): ProductReport|false
    {
        $current = $report->isResolved();
        if ($data['resolved'] !== $current) {
            $report->setResolved($data['resolved']);
            $this->gm->resolveReport($report, $current);
        }

        $this->em->persist($report);
        $this->em->flush();

        return $report;
    }
}