<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductReport>
 */
class ProductReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductReport::class);
    }

    public function findByFilters(array $filters, Product $product): array
    {
        $qb = $this->createQueryBuilder('pr')
            ->leftJoin('pr.user', 'u')
            ->addSelect('u')
            ->andWhere('pr.product = :product')
            ->setParameter('product', $product);

        if (\array_key_exists('resolved', $filters)) {
            $resolved = match ($filters['resolved']) {
                'true'  => true,
                'false' => false,
                'null'  => null,
            };

            if ($resolved === null) {
                $qb->andWhere('pr.resolved IS NULL');
            } else {
                $qb->andWhere('pr.resolved = :resolved')
                ->setParameter('resolved', $resolved);
            }
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return ProductReport[] Returns an array of ProductReport objects
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

    //    public function findOneBySomeField($value): ?ProductReport
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
