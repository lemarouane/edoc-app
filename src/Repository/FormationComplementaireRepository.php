<?php

namespace App\Repository;

use App\Entity\FormationComplementaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationComplementaire>
 *
 * @method FormationComplementaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormationComplementaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormationComplementaire[]    findAll()
 * @method FormationComplementaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationComplementaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationComplementaire::class);
    }

//    /**
//     * @return FormationComplementaire[] Returns an array of FormationComplementaire objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FormationComplementaire
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
