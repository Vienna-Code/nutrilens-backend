<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductReport;
use App\Enum\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<ProductReport>
 */
class ProductReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductReport::class);
    }

    public function findByFilters(array $filters, ?Product $product): array
    {
        $qb = $this->createQueryBuilder('pr')
            ->leftJoin('pr.user', 'u')
            ->addSelect('u')
            ->leftJoin('pr.product', 'p')
            ->addSelect('p');

        if ($product !== null) {
            $qb->andWhere('pr.product = :product')
                ->setParameter('product', $product);
        }

        if (isset($filters['resolved'])) {
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

        if (isset($filters['user'])) {
            $qb->andWhere('pr.user = :user')
                ->setParameter('user', $filters['user']);
        }

        if (isset($filters['types'])) {
            $qb->andWhere('pr.type IN (:types)')
                ->setParameter('types', $filters['types']);
        }

        // Orden
        $filters['orderBy'] ??= 'date_desc';
        [$attr, $ord] = match ($filters['orderBy'] ?? null) {
            'date_asc'      => ['pr.date', 'ASC'],
            'date_desc'     => ['pr.date', 'DESC'],
        };
        $qb->orderBy($attr, $ord);

        // Página
        $filters['page'] ??= 1;
        $qb->setMaxResults(10)
            ->setFirstResult(($filters['page'] - 1) * 10);
        $paginator = new Paginator($qb, true);

        return iterator_to_array($paginator);
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

    public function countAll(?array $types): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if (!empty($types)) {
            $qb->andWhere('r.type IN (:types)')
            ->setParameter('types', $types);
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult();
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
