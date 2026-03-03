<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\Visibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findById(int $id, ?User $user = null): ?Post
    {
        $qb = $this->createQueryBuilder('p')

            // post author
            ->leftJoin('p.user', 'pu')
            ->addSelect('pu')

            // tags
            ->leftJoin('p.tags', 't')
            ->addSelect('t')

            ->andWhere('p.id = :id AND (p.visibility != :vis OR p.user = :user)')
            ->setParameter('id', $id)
            ->setParameter('vis', Visibility::PRIVATE)
            ->setParameter('user', $user);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByFilters(array $filters, ?User $user = null): array
    {
        if (!isset($filters['page'])) {
            $filters['page'] = 1;
        }

        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p');

        if (isset($filters['visibility'])) {
            $qb->andWhere('p.visibility IN (:visibilities)')
                ->setParameter('visibilities', $filters['visibility']);
        } else {
            $qb->andWhere('p.visibility = :vis OR p.user = :user')
                ->setParameter('vis', Visibility::PUBLIC)
                ->setParameter('user', $user);
        }

        // post author
        $qb->leftJoin('p.user', 'pu')
            ->addSelect('pu')

            // tags
            ->leftJoin('p.tags', 't')
            ->addSelect('t')
            
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(10)
            ->setFirstResult(($filters['page']-1)*10);

        $paginator = new Paginator($qb, true);

        return iterator_to_array($paginator);
    }

//    /**
//     * @return Post[] Returns an array of Post objects
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

//    public function findOneBySomeField($value): ?Post
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
