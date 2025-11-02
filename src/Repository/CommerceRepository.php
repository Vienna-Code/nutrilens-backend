<?php

namespace App\Repository;

use App\Entity\Commerce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

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
            ->innerJoin('c.commerceSchedules', 'cs')
            ->addSelect('cs');

        // Rango de latitud-longitud
        if (isset($filters['lat'], $filters['lon'])) {
            [$lat1, $lat2] = explode(',', $filters['lat']);
            [$lon1, $lon2] = explode(',', $filters['lon']);
            $qb->andWhere('(c.coordsLat BETWEEN :lat1 AND :lat2) AND (c.coordsLon BETWEEN :lon1 AND :lon2)')
            ->setParameter('lat1', $lat1)
            ->setParameter('lat2', $lat2)
            ->setParameter('lon1', $lon1)
            ->setParameter('lon2', $lon2);
        }
        
        // Comercios verificados
        if (!isset($filters['unverified'])) {
            $qb->andWhere('c.verified = :verified')->setParameter('verified', 1);
        }

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
