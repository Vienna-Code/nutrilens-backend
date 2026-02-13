<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use App\Enum\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFilters($filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.commerce', 'c')
            ->leftJoin('p.aptFor', 'pr')
            ->addSelect('c')
            ->addSelect('pr');
        
        if (!empty($filters['commerce'])) {
            $qb->andWhere('c.id = :commerceId')
                ->setParameter('commerceId', $filters['commerce']);
        }

        if (!isset($filters['unverified'])) {
            $qb->andWhere('p.verified = :verified')
                ->setParameter('verified', 1);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithReports(int $id, ?bool $resolved = null): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('p.productReports', 'pr')
            ->addSelect('pr');

        if ($resolved === null) {
            $qb->andWhere('pr.resolved IS NULL');
        } else {
            $qb->andWhere('pr.resolved = :res')
                ->setParameter('res', $resolved);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByReports(User $user, ReportType $type): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.productReports', 'pr')
            ->leftJoin('p.aptFor', 'pa')
            ->addSelect('pr')
            ->addSelect('pa')
            ->andWhere('pr.type = :type AND pr.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
