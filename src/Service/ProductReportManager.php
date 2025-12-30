<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\Product;
use App\Entity\ProductReport;
use App\Entity\User;
use App\Enum\ReportType;
use Doctrine\ORM\EntityManagerInterface;

class ProductReportManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
    ) {}

    public function create(array &$data, Product &$product, User &$user): ProductReport
    {
        $productReport = new ProductReport();
        $productReport->setContent($data['content'] ?? null);
        $productReport->setType(ReportType::tryFrom($data['type']));

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

    public function delete(Commerce $commerce): void
    {
        
    }
}