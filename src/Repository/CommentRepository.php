<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\Visibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findById(int $id, ?User $user = null): ?Comment
    {
        $qb = $this->createQueryBuilder('c')

            // comment author
            ->leftJoin('c.user', 'cu')
            ->addSelect('cu')

            // replies
            ->leftJoin('c.replies', 'r1')
            ->addSelect('r1')
            ->leftJoin('r1.replies', 'r2')
            ->addSelect('r2')
            ->leftJoin('r2.replies', 'r3')
            ->addSelect('r3')
            ->leftJoin('r1.user', 'r1u')->addSelect('r1u')
            ->leftJoin('r2.user', 'r2u')->addSelect('r2u')
            ->leftJoin('r3.user', 'r3u')->addSelect('r3u')

            ->andWhere('c.id = :id AND (c.visibility != :vis OR c.user = :user)')
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

        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.visibility = :vis OR c.user = :user')
            ->setParameter('vis', Visibility::PUBLIC)
            ->setParameter('user', $user)

            // comment author
            ->leftJoin('c.user', 'cu')
            ->addSelect('cu')

            // replies
            ->leftJoin('c.replies', 'r1')
            ->addSelect('r1')
            ->leftJoin('r1.replies', 'r2')
            ->addSelect('r2')
            ->leftJoin('r2.replies', 'r3')
            ->addSelect('r3')
            ->leftJoin('r1.user', 'r1u')->addSelect('r1u')
            ->leftJoin('r2.user', 'r2u')->addSelect('r2u')
            ->leftJoin('r3.user', 'r3u')->addSelect('r3u')
            
            ->andWhere('c.post = :post')
            ->andWhere('c.replyingTo IS NULL')
            ->setParameter('post', $filters['post'])
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(20)
            ->setFirstResult(($filters['page']-1)*20);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Comment[] Returns an array of Comment objects
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

    //    public function findOneBySomeField($value): ?Comment
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
