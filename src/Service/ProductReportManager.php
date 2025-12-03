<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
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
        $productReport->setContent($data['content']);
        $productReport->setType(ReportType::tryFrom($data['type']));

        $product->addProductReport($productReport);
        $user->addProductReport($productReport);

        $this->em->persist($product);
        $this->em->persist($user);
        $this->em->flush();

        return $productReport;
    }

    public function update(array &$data, Commerce &$commerce, User &$user): CommerceReport|false
    {
        return false;
    }

    public function delete(Commerce $commerce): void
    {
        
    }
}