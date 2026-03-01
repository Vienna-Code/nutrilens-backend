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

        if (isset($filters['name'])) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['minPrice']) || isset($filters['maxPrice'])) {
                $minPrice = $filters['minPrice'] ?? 0;
                $maxPrice = $filters['maxPrice'] ?? 2147483647;
    
                $qb->andWhere('(p.price BETWEEN :minPrice AND :maxPrice)')
                    ->setParameter('minPrice', $minPrice)
                    ->setParameter('maxPrice', $maxPrice);
        }

        if (!empty($filters['restrictions'])) {
            $subQb = $this->getEntityManager()->createQueryBuilder()
                ->select('p2.id')
                ->from(Product::class, 'p2')
                ->leftJoin('p2.aptFor', 'pr2')
                ->where('pr2.restriction IN (:restrictions)')
                ->groupBy('p2.id')
                ->having('COUNT(DISTINCT pr2.restriction) = :count');

            $qb->andWhere($qb->expr()->in('p.id', $subQb->getDQL()))
                ->setParameter('restrictions', $filters['restrictions'])
                ->setParameter('count', count($filters['restrictions']));
        }

        if (!empty($filters['category'])) {
            $qb->andWhere('p.category IN (:categories)')
            ->setParameter('categories', $filters['category']);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithReports(int $id, ?bool $resolved = null): ?Product
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id);

        if ($resolved === null) {
            $qb->leftJoin('p.productReports', 'pr', 'WITH', 'pr.resolved IS NULL');
        } else {
            $qb->leftJoin('p.productReports', 'pr', 'WITH', 'pr.resolved = :res')
                ->setParameter('res', $resolved);
        }

        $qb->addSelect('pr');

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
