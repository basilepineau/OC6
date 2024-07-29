<?php

namespace App\Repository;

use App\Entity\CommentMain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentMain>
 */
class CommentMainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentMain::class);
    }

    /**
     * @return CommentMain[] Returns an array of CommentMain objects
     */
    public function findByTrickOrderedByDate($trickId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.trick = :trickId')
            ->setParameter('trickId', $trickId)
            ->orderBy('c.createdAt', 'DESC') // Pour le plus récent au plus vieux
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return CommentMain[] Returns an array of CommentMain objects
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

//    public function findOneBySomeField($value): ?CommentMain
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
