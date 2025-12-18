<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserGamification;
use App\Entity\Commerce;
use App\Entity\Product;
use App\Entity\CommerceReport;
use App\Entity\ProductReport;
use App\Enum\ReportType;
use App\Repository\CommerceReportRepository;
use App\Repository\ProductReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;

class GamificationManager
{
    private $points = [
        'commerce' => [
            'submitter' => 50,
            'confirmation' => 30,
            'validation' => 20,
        ],
        'product' => [
            'submitter' => 30,
            'confirmation' => 20,
            'validation' => 15,
        ],
        'review' => 3,
        'post' => 1,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private CommerceReportRepository $crr,
        private ProductReportRepository $prr,
    ) {}

    public function verifyCommerce(Commerce &$commerce, bool $resolved = true): void
    {
        $commerceReports = $this->crr->findByFilters(['resolved' => $resolved], $commerce);
        foreach ($commerceReports as $report) {
            $gamification = new UserGamification();
            $user = $report->getUser();
            $report->setResolved(true);

            if ($report->getType() === ReportType::SUBMISSION) {
                $gamification->setEvent('Commerce submission of ID '.$report->getId().' was verified.');
                $gamification->setPoints($this->points['commerce']['submitter']);
                $user->addUserGamification($gamification);
            }

            if ($report->getType() === ReportType::CONFIRMATION) {
                $gamification->setEvent('Commerce confirmation of ID '.$report->getId().' was verified.');
                $gamification->setPoints($this->points['commerce']['confirmation']);
                $user->addUserGamification($gamification);
            }

            $this->em->persist($report);
        }
    }

    public function verifyProduct(Product &$product, bool $resolved = true): void
    {
        $productReports = $this->prr->findByFilters(['resolved' => $resolved], $product);
        foreach ($productReports as $report) {
            $gamification = new UserGamification();
            $user = $report->getUser();
            $report->setResolved(true);

            if ($report->getType() === ReportType::SUBMISSION) {
                $gamification->setEvent("Product submission of ID {$report->getId()} was verified.");
                $gamification->setPoints($this->points['product']['submitter']);
                $user->addUserGamification($gamification);
            }

            if ($report->getType() === ReportType::CONFIRMATION) {
                $gamification->setEvent("Product confirmation of ID {$report->getId()} was verified.");
                $gamification->setPoints($this->points['product']['confirmation']);
                $user->addUserGamification($gamification);
            }

            $this->em->persist($report);
        }
    }

    public function resolveCommerceReport(CommerceReport $report): void
    {
        $user = $report->getUser();
        $gamification = new UserGamification();

        if ($report->getType() === ReportType::ISSUE) {
            $gamification->setEvent("Commerce issue report of ID {$report->getId()} was resolved.");
            $gamification->setPoints($this->points['commerce']['validation']);
            $user->addUserGamification($gamification);
        }
        
        if ($report->getType() === ReportType::MODIFICATION) {
            $gamification->setEvent("Commerce modification of ID {$report->getId()} was resolved.");
            $gamification->setPoints($this->points['commerce']['validation']);
            $user->addUserGamification($gamification);
        }

        if ($report->getType() === ReportType::REBUTTAL) {
            $gamification->setEvent("Commerce rebuttal of ID {$report->getId()} was resolved.");
            $gamification->setPoints($this->points['commerce']['validation']);
            $user->addUserGamification($gamification);
        }
    }
}