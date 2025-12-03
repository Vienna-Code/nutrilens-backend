<?php

namespace App\Repository;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
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

        return $qb->getQuery()->getResult();
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
