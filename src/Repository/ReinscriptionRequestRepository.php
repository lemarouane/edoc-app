<?php

namespace App\Repository;

use App\Entity\ReinscriptionRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReinscriptionRequest>
 *
 * @method ReinscriptionRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReinscriptionRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReinscriptionRequest[]    findAll()
 * @method ReinscriptionRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReinscriptionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReinscriptionRequest::class);
    }

//    /**
//     * @return ReinscriptionRequest[] Returns an array of ReinscriptionRequest objects
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

//    public function findOneBySomeField($value): ?ReinscriptionRequest
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
