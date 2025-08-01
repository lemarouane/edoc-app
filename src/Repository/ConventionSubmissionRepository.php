<?php

namespace App\Repository;

use App\Entity\ConventionSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConventionSubmission>
 *
 * @method ConventionSubmission|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConventionSubmission|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConventionSubmission[]    findAll()
 * @method ConventionSubmission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConventionSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConventionSubmission::class);
    }

//    /**
//     * @return ConventionSubmission[] Returns an array of ConventionSubmission objects
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

//    public function findOneBySomeField($value): ?ConventionSubmission
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
