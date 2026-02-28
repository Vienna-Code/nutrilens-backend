<?php

namespace App\Repository;

use App\Entity\Commerce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Enum\ReportType;

/**
 * @extends ServiceEntityRepository<Commerce>
 */
class CommerceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commerce::class);
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.commerceReports', 'cr')
            ->leftJoin('c.commerceSchedules', 'cs')
            ->leftJoin('c.products', 'p')
            ->addSelect('cr')
            ->addSelect('cs')
            ->addSelect('p');

        // Rango de latitud-longitud
        if (isset($filters['lat'], $filters['lon'])) {
            [$lat1, $lat2] = explode(',', $filters['lat']);
            [$lon1, $lon2] = explode(',', $filters['lon']);
            if ($lat1 > $lat2) [$lat1, $lat2] = [$lat2, $lat1];
            if ($lon1 > $lon2) [$lon1, $lon2] = [$lon2, $lon1];
            $qb->andWhere('(c.coordsLat BETWEEN :lat1 AND :lat2) AND (c.coordsLon BETWEEN :lon1 AND :lon2)')
                ->setParameter('lat1', $lat1)
                ->setParameter('lat2', $lat2)
                ->setParameter('lon1', $lon1)
                ->setParameter('lon2', $lon2);
        }

        // Nombre
        if (isset($filters['name'])) {
            $qb->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $filters['name'] . '%');
        }

        // Join con productos
        if (
            isset($filters['minPrice']) ||
            isset($filters['maxPrice']) ||
            isset($filters['restrictions'])
        ) {
            // Rango de precios
            if (isset($filters['minPrice']) || isset($filters['maxPrice'])) {
                $minPrice = $filters['minPrice'] ?? 0;
                $maxPrice = $filters['maxPrice'] ?? 2147483647;
    
                $qb->andWhere('(p.price BETWEEN :minPrice AND :maxPrice) OR p.id IS NULL')
                    ->setParameter('minPrice', $minPrice)
                    ->setParameter('maxPrice', $maxPrice);
            }

            // Restricciones alimentarias
            if (isset($filters['restrictions'])) {
                $restrictions = $filters['restrictions'];

                $subqb = $this->createQueryBuilder('c2')
                    ->select('c2.id')
                    ->leftJoin('c2.products', 'p2')
                    ->leftJoin('p2.aptFor', 'pr2')
                    ->andWhere('pr2.restriction IN (:restrictions)')
                    ->groupBy('c2.id')
                    ->having('COUNT(DISTINCT pr2.restriction) = :neededCount');

                $qb->andWhere($qb->expr()->in('c.id', $subqb->getDQL()))
                    ->setParameter('restrictions', $restrictions)
                    ->setParameter('neededCount', count($restrictions));
            }
        }

        // Tipos de comercio
        if (isset($filters['commerceTypes'])) {
            $commerceTypes = $filters['commerceTypes'];
            $qb->andWhere('c.type IN (:types)')
                ->setParameter('types', $commerceTypes);
        }

        // Orden
        if (isset($filters['orderBy'])) {
            if (str_contains($filters['orderBy'], 'rating')) {
                $qb->addSelect('(c.positiveReviews / NULLIF(c.totalReviews, 0)) * 100 AS HIDDEN rating')
                    ->andWhere('c.positiveReviews > 0');
            }
            if (str_contains($filters['orderBy'], 'price')) {
                $qb->andWhere('p.id IS NOT NULL');
            }
            [$attr, $ord] = match ($filters['orderBy'] ?? null) {
                'name_asc'      => ['c.name', 'ASC'],
                'name_desc'     => ['c.name', 'DESC'],
                'rating_asc'    => ['rating', 'ASC'],
                'rating_desc'   => ['rating', 'DESC'],
                'price_asc'     => ['p.price', 'ASC'],
                'price_desc'    => ['p.price', 'DESC'],
                default         => ['c.id', 'ASC'],
            };
            $qb->orderBy($attr, $ord);
        }
        
        // Comercios verificados
        if (!isset($filters['unverified'])) {
            $qb->andWhere('c.verified = :verified')->setParameter('verified', 1);
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithReports(int $id, ?bool $resolved = null): ?Commerce
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id);

        if ($resolved === null) {
            $qb->leftJoin('c.commerceReports', 'cr', 'WITH', 'cr.resolved IS NULL');
        } else {
            $qb->leftJoin('c.commerceReports', 'cr', 'WITH', 'cr.resolved = :res')
                ->setParameter('res', $resolved);
        }

        $qb->addSelect('cr');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByReports(User $user, ReportType $type): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.commerceReports', 'cr')
            ->leftJoin('c.commerceSchedules', 'cs')
            ->addSelect('cr')
            ->addSelect('cs')
            ->andWhere('cr.type = :type AND cr.user = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Commerce[] Returns an array of Commerce objects
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

    //    public function findOneBySomeField($value): ?Commerce
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
