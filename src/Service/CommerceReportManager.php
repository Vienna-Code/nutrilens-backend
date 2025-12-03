<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Entity\CommerceSchedule;
use App\Entity\User;
use App\Enum\CommerceType;
use App\Enum\ReportType;
use App\Enum\UserRank;
use Doctrine\ORM\EntityManagerInterface;

class CommerceReportManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
    ) {}

    public function create(array &$data, Commerce &$commerce, User &$user): CommerceReport
    {
        $commerceReport = new CommerceReport();
        $commerceReport->setContent($data['content']);
        $commerceReport->setType(ReportType::tryFrom($data['type']));

        $commerce->addCommerceReport($commerceReport);
        $user->addCommerceReport($commerceReport);

        $this->em->persist($commerce);
        $this->em->persist($user);
        $this->em->flush();

        return $commerceReport;
    }

    public function update(array &$data, Commerce &$commerce, User &$user): CommerceReport|false
    {
        return false;
    }

    public function delete(Commerce $commerce): void
    {
        
    }
}