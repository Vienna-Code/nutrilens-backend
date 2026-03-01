<?php

namespace App\Repository;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Entity\User;
use App\Enum\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommerceReport>
 */
class CommerceReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommerceReport::class);
    }

    public function findByFilters(array $filters, Commerce $commerce): array
    {
        $qb = $this->createQueryBuilder('cr')
            ->leftJoin('cr.user', 'u')
            ->addSelect('u')
            ->andWhere('cr.commerce = :commerce')
            ->setParameter('commerce', $commerce);

        if (\array_key_exists('resolved', $filters)) {
            $resolved = match ($filters['resolved']) {
                'true'  => true,
                'false' => false,
                'null'  => null,
                default => $filters['resolved'],
            };

            if ($resolved === null) {
                $qb->andWhere('cr.resolved IS NULL');
            } else {
                $qb->andWhere('cr.resolved = :resolved')
                    ->setParameter('resolved', $resolved);
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findOppositeIfExists($type, User $user, Commerce $commerce): CommerceReport|null
    {
        $type = match ($type) {
            ReportType::CONFIRMATION->value => ReportType::REBUTTAL->value,
            ReportType::REBUTTAL->value     => ReportType::CONFIRMATION->value,
            default                         => null,
        };

        if (!$type) {
            return null;
        }

        $qb = $this->createQueryBuilder('cr')
            ->andWhere('cr.user = :user')
            ->setParameter('user', $user)
            ->andWhere('cr.commerce = :commerce')
            ->setParameter('commerce', $commerce)
            ->andWhere('cr.type = :type')
            ->setParameter('type', $type);

        return $qb->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return CommerceReport[] Returns an array of CommerceReport objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CommerceReport
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
