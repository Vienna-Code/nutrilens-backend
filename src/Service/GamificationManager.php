<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserGamification;
use App\Entity\Commerce;
use App\Entity\Product;
use App\Entity\CommerceReport;
use App\Entity\ProductReport;
use App\Enum\ReportType;
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
    ) {}

    public function verifyCommerce(Commerce &$commerce): void
    {
        foreach ($commerce->getCommerceReports() as $report) {
            $gamification = new UserGamification();
            $user = $report->getUser();

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
        }
    }

    public function verifyProduct(Product &$product): void
    {
        foreach ($product->getProductReports() as $report) {
            $gamification = new UserGamification();
            $user = $report->getUser();

            if ($report->getType() === ReportType::SUBMISSION) {
                $gamification->setEvent('Product submission of ID '.$report->getId().' was verified.');
                $gamification->setPoints($this->points['product']['submitter']);
                $user->addUserGamification($gamification);
            }

            if ($report->getType() === ReportType::CONFIRMATION) {
                $gamification->setEvent('Product confirmation of ID '.$report->getId().' was verified.');
                $gamification->setPoints($this->points['product']['confirmation']);
                $user->addUserGamification($gamification);
            }
        }
    }
}