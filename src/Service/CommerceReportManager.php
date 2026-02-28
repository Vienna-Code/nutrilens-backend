<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Entity\User;
use App\Enum\ReportType;
use App\Repository\ImageRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CommerceReportManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, Commerce &$commerce, User &$user): CommerceReport
    {
        $commerceReport = new CommerceReport();
        $commerceReport->setContent($data['content'] ?? null);
        $commerceReport->setType(ReportType::tryFrom($data['type']));
        if (isset($data['image']) && $data['image'] != null) {
            if (!$this->imageRepository->find($data['image'])) {
                throw new \InvalidArgumentException('La imagen no fue encontrada');
            }
            $commerceReport->setImagePath($data['image']);
        }

        $commerce->addCommerceReport($commerceReport);
        $user->addCommerceReport($commerceReport);

        // Ver si se puede verificar el comercio automáticamente
        $confirmations = 0;
        foreach ($commerce->getCommerceReports() as $report) {
            if ($report->getType() === ReportType::CONFIRMATION) {
                $confirmations++;
            }
            if ($report->getType() === ReportType::REBUTTAL) {
                // No verificar si hay un reporte negativo
                $confirmations = 0;
                break;
            }
        }
        if ($confirmations >= 3) {
            // Verificar commercio automáticamente al tener 3 reportes de existencia
            $verificationReport = new CommerceReport();
            $verificationReport->setContent('Comercio obtuvo tres reportes de confirmaciones de existencia.');
            $verificationReport->setType(ReportType::VERIFICATION);
            $verificationReport->setDate(
                (new DateTimeImmutable())->modify('+1 second')
            );
            $commerce->addCommerceReport($verificationReport);
            $commerce->setVerified(true);
            
            $this->gm->verifyCommerce($commerce);
        }

        $this->em->persist($commerce);
        $this->em->persist($user);
        $this->em->flush();

        return $commerceReport;
    }

    public function update(array &$data, CommerceReport &$report): CommerceReport|false
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