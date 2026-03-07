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

    public function verifyCommerce(Commerce &$commerce, ?bool $resolved = null): void
    {
        $commerceReports = $this->crr->findBy([
            'resolved' => $resolved,
            'commerce' => $commerce
        ]);
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

            $this->em->persist($user);
            $this->em->persist($report);
            $this->em->flush();
        }
    }

    public function verifyProduct(Product &$product, ?bool $resolved = null): void
    {
        $productReports = $this->prr->findBy([
            'resolved' => $resolved,
            'product' => $product
        ]);
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

            $this->em->persist($user);
            $this->em->persist($report);
            $this->em->flush();
        }
    }

    public function resolveReport(CommerceReport|ProductReport $report, ?bool $current = null): void
    {
        if ($report->isResolved() === false && $current !== true) return;
        if ($report->isResolved() === null && $current === false) return;
        if (!\in_array($report->getType(), [ReportType::ISSUE, ReportType::MODIFICATION, ReportType::REBUTTAL])) return;

        $user = $report->getUser();
        if (!$user) return;
        $gamification = new UserGamification();
        $type = ($report instanceof CommerceReport) ? "Commerce" : "Product";

        if ($report->isResolved() === true) {
            $reward = $this->points[strtolower($type)]['validation'];
            $text = "validated";
        } else {
            $reward = -($this->points[strtolower($type)]['validation']);
            $text = "unvalidated";
        }

        if ($report->getType() === ReportType::ISSUE) {
            $gamification->setEvent("$type issue report of ID {$report->getId()} was $text.");
        }
        
        if ($report->getType() === ReportType::MODIFICATION) {
            $gamification->setEvent("$type modification of ID {$report->getId()} was $text.");
        }

        if ($report->getType() === ReportType::REBUTTAL) {
            $gamification->setEvent("$type rebuttal of ID {$report->getId()} was $text.");
        }

        $gamification->setPoints($reward);
        $user->addUserGamification($gamification);
    }
}