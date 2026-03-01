<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductReport;
use App\Enum\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Proxies\__CG__\App\Entity\User;

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
                default => $filters['resolved'],
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

    public function findOppositeIfExists($type, User $user, Product $product): ProductReport|null
    {
        $type = match ($type) {
            ReportType::CONFIRMATION->value => ReportType::REBUTTAL->value,
            ReportType::REBUTTAL->value     => ReportType::CONFIRMATION->value,
            default                         => null,
        };

        if (!$type) {
            return null;
        }

        $qb = $this->createQueryBuilder('pr')
            ->andWhere('pr.user = :user')
            ->setParameter('user', $user)
            ->andWhere('pr.product = :product')
            ->setParameter('product', $product)
            ->andWhere('pr.type = :type')
            ->setParameter('type', $type);

        return $qb->getQuery()->getOneOrNullResult();
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
