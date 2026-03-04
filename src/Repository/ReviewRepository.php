<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByFilters($filters): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->orderBy('r.useful', 'DESC');

        if (!empty($filters['commerce'])) {
            $qb->andWhere('IDENTITY(r.commerce) = :commerceId AND r.visibility = :visibility')
                ->setParameter('commerceId', $filters['commerce'])
                ->setParameter('visibility', 'public');
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneByIds($data): ?Review
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->andWhere('IDENTITY(r.commerce) = :commerceId')
            ->setParameter('commerceId', $data['commerceId']);

        return $qb->getQuery()->getResult();
    }

    public function countAllByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(DISTINCT r.id)')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByPositive(User $user): array
    {
        return  $this->createQueryBuilder('r')
            ->select('r.positive as positive, COUNT(r.id) as total')
            ->groupBy('r.positive')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
    }

//    /**
//     * @return Review[] Returns an array of Review objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Review
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
