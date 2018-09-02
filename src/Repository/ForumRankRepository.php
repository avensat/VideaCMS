<?php

namespace App\Repository;

use App\Entity\ForumRank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ForumRank|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumRank|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumRank[]    findAll()
 * @method ForumRank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumRankRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ForumRank::class);
    }

//    /**
//     * @return ForumRank[] Returns an array of ForumRank objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ForumRank
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param $userID
     * @return array
     */
    public function getRanks(){

        $ranks = $this->createQueryBuilder('p')
            ->orderBy("p.id", "ASC")
            ->getQuery()
            ->getArrayResult();

        return $ranks;
    }

}
