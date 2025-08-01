<?php

namespace App\Repository;

use App\Entity\RapportPfe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RapportPfe>
 *
 * @method RapportPfe|null find($id, $lockMode = null, $lockVersion = null)
 * @method RapportPfe|null findOneBy(array $criteria, array $orderBy = null)
 * @method RapportPfe[]    findAll()
 * @method RapportPfe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RapportPfeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RapportPfe::class);
    }

//    /**
//     * @return RapportPfe[] Returns an array of RapportPfe objects
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

//    public function findOneBySomeField($value): ?RapportPfe
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
