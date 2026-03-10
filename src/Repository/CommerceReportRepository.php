<?php

namespace App\Repository;

use App\Entity\Commerce;
use App\Entity\CommerceReport;
use App\Entity\User;
use App\Enum\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findByFilters(array $filters, ?Commerce $commerce): array
    {
        $qb = $this->createQueryBuilder('cr')
            ->leftJoin('cr.user', 'u')
            ->addSelect('u')
            ->leftJoin('cr.commerce', 'c')
            ->addSelect('c');

        if ($commerce !== null) {
            $qb->andWhere('cr.commerce = :commerce')
                ->setParameter('commerce', $commerce);
        }

        if (isset($filters['resolved'])) {
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

        if (isset($filters['user'])) {
            $qb->andWhere('cr.user = :user')
                ->setParameter('user', $filters['user']);
        }

        if (isset($filters['types'])) {
            $qb->andWhere('cr.type IN (:types)')
                ->setParameter('types', $filters['types']);
        }

        // Orden
        $filters['orderBy'] ??= 'date_desc';
        [$attr, $ord] = match ($filters['orderBy'] ?? null) {
            'date_asc'      => ['cr.date', 'ASC'],
            'date_desc'     => ['cr.date', 'DESC'],
        };
        $qb->orderBy($attr, $ord);

        // Página
        $filters['page'] ??= 1;
        $qb->setMaxResults(10)
            ->setFirstResult(($filters['page'] - 1) * 10);
        $paginator = new Paginator($qb, true);

        return iterator_to_array($paginator);
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

    public function countAll(?array $data = []): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if (isset($data['types']) && !empty($data['types'])) {
            $qb->andWhere('r.type IN (:types)')
            ->setParameter('types', $data['types']);
        }

        if (isset($data['resolved'])) {
            if ($data['resolved'] === 'null') {
                $qb->andWhere('r.resolved IS NULL');
            } else {
                $data['resolved'] = match ($data['resolved']) {
                    'true' => true,
                    'false' => false,
                };
                $qb->andWhere('r.resolved = :res')
                ->setParameter('res', $data['resolved']);
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
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
