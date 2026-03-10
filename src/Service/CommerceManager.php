<?php

namespace App\Service;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Entity\CommerceSchedule;
use App\Entity\User;
use App\Enum\CommerceType;
use App\Enum\ReportType;
use App\Enum\UserRank;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class CommerceManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private GamificationManager $gm,
        private ImageRepository $imageRepository,
    ) {}

    public function create(array &$data, User &$user): Commerce
    {
        $data['verifiedUser'] =
            $user->getUserRank() === UserRank::PLATINUM || // Usuario con rango platino
            \in_array('ROLE_ADMIN', $user->getRoles()); // Administrador
        
        $data['type'] = CommerceType::tryFrom($data['type'] ?? null);
        $commerce = new Commerce();
        $commerce->setName($data['name']);
        $commerce->setType($data['type']);
        $commerce->setCoordsLat($data['coordsLat']);
        $commerce->setCoordsLon($data['coordsLon']);
        $commerce->setAddress($data['address']);
        $commerce->setContactInfo($data['contactInfo'] ?? null);
        $commerce->setPaymentMethods($data['paymentMethods'] ?? null);
        $commerce->setVerified($data['verifiedUser']);
        
        $submissionReport = new CommerceReport();
        $submissionReport->setUser($user);
        $submissionReport->setType(ReportType::SUBMISSION);
        $commerce->addCommerceReport($submissionReport);
        
        if ($data['verifiedUser']) {
            $verificationReport = new CommerceReport();
            $verificationReport->setType(ReportType::VERIFICATION);
            $commerce->addCommerceReport($verificationReport);
        }
        
        // Horarios
        $data['commerceSchedules'] ??= [];
        foreach ($data['commerceSchedules'] as &$scheduleData) {
            $schedule = new CommerceSchedule();
            $schedule->setWeekday($scheduleData['weekday']);
            $schedule->setOpensAt(new \DateTimeImmutable($scheduleData['opensAt']));
            $schedule->setClosesAt(new \DateTimeImmutable($scheduleData['closesAt']));
            $commerce->addCommerceSchedule($schedule);
        }

        // Imágenes
        $uuids = $data['images'] ?? [];
        if (!empty($uuids)) {
            $uuidObjects = array_map(
                fn (string $uuid) => Uuid::fromString($uuid),
                $uuids
            );
            $images = $this->imageRepository->findBy([
                'id' => $uuidObjects,
            ]);
            if (\count($images) !== \count($uuids)) {
                throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
            }
            foreach ($images as $image) {
                if ($image->getUser() !== $user && \in_array('ROLE_ADMIN', $user->getRoles())) {
                    throw new \InvalidArgumentException('Imagen de ID ' . $image->getId()->toRfc4122() . ' no fue subida por el usuario.');
                }
            }
            $commerce->setCommerceImages($data['images']);
        }

        $this->em->persist($commerce);
        $this->em->flush();

        return $commerce;
    }

    public function update(array &$data, Commerce &$commerce, User &$user): Commerce|false
    {
        $userRank = $user->getUserRank();
        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoles());
        
        // Usuario no es Gold, Platinum, o admin
        if (!(\in_array($userRank, [UserRank::PLATINUM, UserRank::GOLD]) || $isAdmin)) {
            return false;
        }
        
        $submissionReport = new CommerceReport();
        $submissionReport->setUser($user);
        $submissionReport->setType(ReportType::MODIFICATION);
        
        $commerce->setContactInfo($data['contactInfo'] ?? $commerce->getContactInfo());
        $commerce->setPaymentMethods($data['paymentMethods'] ?? $commerce->getPaymentMethods());

        // Horarios
        if (isset($data['commerceSchedules'])) {
            foreach ($commerce->getCommerceSchedules() as $schedule) {
                $commerce->removeCommerceSchedule($schedule);
            }
            foreach ($data['commerceSchedules'] as &$scheduleData) {
                $schedule = new CommerceSchedule();
                $schedule->setWeekday($scheduleData['weekday']);
                $schedule->setOpensAt(new \DateTimeImmutable($scheduleData['opensAt']));
                $schedule->setClosesAt(new \DateTimeImmutable($scheduleData['closesAt']));
                $commerce->addCommerceSchedule($schedule);
            }
        }

        // Imágenes
        if (isset($data['images'])) {
            $incoming = $data['images'] ?? [];
            $current  = $commerce->getCommerceImages() ?? [];
            $isAdmin  = \in_array('ROLE_ADMIN', $user->getRoles(), true);
            $toAdd    = array_diff($incoming, $current);
            $toRemove = array_diff($current, $incoming);

            if (!$isAdmin && (!empty($toAdd) || !empty($toRemove))) {
                $affectedUuids = array_unique(\array_merge($toAdd, $toRemove));
                $uuidObjects = array_map(
                    fn (string $uuid) => Uuid::fromString($uuid),
                    $affectedUuids
                );
                $images = $this->imageRepository->findBy([
                    'id' => $uuidObjects,
                ]);
                
                if (\count($images) !== \count($affectedUuids)) {
                    throw new \InvalidArgumentException('Una o más imagenes no fueron encontradas.');
                }
            }

            $commerce->setCommerceImages(array_values(array_unique($incoming)));
        }

        // Funciones solo para admin
        if ($isAdmin) {
            $data['type'] = CommerceType::tryFrom($data['type'] ?? null);
            $commerce->setName($data['name'] ?? $commerce->getName());
            $commerce->setType($data['type'] ?? $commerce->getType());
            $commerce->setCoordsLat($data['coordsLat'] ?? $commerce->getCoordsLat());
            $commerce->setCoordsLon($data['coordsLon'] ?? $commerce->getCoordsLon());
            $commerce->setAddress($data['address'] ?? $commerce->getAddress());
        }

        // Verificacion del comercio
        if (
            isset($data['verified']) &&
            ($userRank === UserRank::PLATINUM || $isAdmin) &&
            $commerce->isVerified() !== $data['verified']
        ) {
            $commerce->setVerified($data['verified']);
            if ($data['verified']) {
                $submissionReport->setType(ReportType::VERIFICATION);
            } else {
                $submissionReport->setType(ReportType::UNVERIFICATION);
            }
        }

        $commerce->addCommerceReport($submissionReport);
        if ($submissionReport->getType() === ReportType::VERIFICATION) {
            $this->gm->verifyCommerce($commerce);
        }
        
        $this->em->persist($commerce);
        $this->em->flush();

        return $commerce;
    }

    public function delete(Commerce $commerce): void
    {
        $this->em->remove($commerce);
        $this->em->flush();
    }
}