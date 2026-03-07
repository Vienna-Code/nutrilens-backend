<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use ErrorException;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($filters['email'])) {
            $qb->andWhere('u.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (isset($filters['minPoints']) || isset($filters['maxPoints'])) {
            $minPoints = $filters['minPoints'] ?? 0;
            $maxPoints = $filters['maxPoints'] ?? 2147483647;

            $qb->andWhere('(u.points BETWEEN :minPoints AND :maxPoints)')
                ->setParameter('minPoints', $minPoints)
                ->setParameter('maxPoints', $maxPoints);
        }

        // Orden
        if (!isset($filters['orderBy']) && isset($filters['username'])) {
            $qb->addSelect("
                CASE
                    WHEN u.username LIKE :start THEN 1
                    WHEN u.username LIKE :like THEN 2
                    ELSE 3
                END AS HIDDEN relevance
            ")
            ->setParameter('start', $filters['username'] . '%')
            ->setParameter('like', '%' . $filters['username'] . '%')
            ->orderBy('relevance', 'ASC')
            ->addOrderBy('u.username', 'ASC');
        } else {
            [$attr, $ord] = match ($filters['orderBy'] ?? null) {
                'date_asc',     => ['u.createdAt', 'ASC'],
                'date_desc',    => ['u.createdAt', 'DESC'],
                'points_asc',   => ['u.points', 'DESC'],
                'points_desc',  => ['u.points', 'ASC'],
                default         => ['u.id', 'ASC'],
            };
            $qb->orderBy($attr, $ord);
        }

        $filters['page'] ??= 1;
        $qb->setMaxResults(10)
            ->setFirstResult(($filters['page'] - 1) * 10);

        $paginator = new Paginator($qb, true);

        return iterator_to_array($paginator);
    }

    public function findOneByIdentifier(string $field, mixed $identifier): ?User
    {
        if (
            $field !== 'username' &&
            $field !== 'email' &&
            $field !== 'id'
        ) {
            throw new ErrorException("Invalid identifier '$field' provided for findUserByIdentifier.");
        }

        return $this->createQueryBuilder('u')
            ->andWhere("u.$field = :id")
            ->setParameter('id', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByRank(): array
    {
        return $this->createQueryBuilder('u')
            ->select("
                CASE
                    WHEN u.points < 100 THEN 'bronze'
                    WHEN u.points < 400 THEN 'silver'
                    WHEN u.points < 1000 THEN 'gold'
                    ELSE 'platinum'
                END AS rank,
                COUNT(u.id) AS total
            ")
            ->groupBy('rank')
            ->getQuery()
            ->getArrayResult();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
