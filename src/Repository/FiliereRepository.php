<?php

namespace App\Repository;

use App\Entity\Customer\Filiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Filiere>
 *
 * @method Filiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Filiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Filiere[]    findAll()
 * @method Filiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FiliereRepository extends EntityRepository
{
    
    public function save(Filiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Filiere $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return Filiere[] Returns an array of Filiere objects
    */
   public function findByCycle(): array
   {
       return $this->createQueryBuilder('f')
            ->leftJoin('f.cycle', 'c')
            ->addSelect('c')
            ->andWhere('c.id = :val')
            ->setParameter('val', 2)
            ->andWhere('f.id < :val1')
            ->setParameter('val1', 7)
            ->orWhere('f.id = :val2')
            ->setParameter('val2', 15)
            ->getQuery()
            ->getResult()
       ;
   }
   
//    public function findOneBySomeField($value): ?Filiere
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
