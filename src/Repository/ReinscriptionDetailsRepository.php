<?php

namespace App\Repository;

use App\Entity\ReinscriptionDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReinscriptionDetails>
 *
 * @method ReinscriptionDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReinscriptionDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReinscriptionDetails[]    findAll()
 * @method ReinscriptionDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReinscriptionDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReinscriptionDetails::class);
    }

//    /**
//     * @return ReinscriptionDetails[] Returns an array of ReinscriptionDetails objects
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

//    public function findOneBySomeField($value): ?ReinscriptionDetails
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
