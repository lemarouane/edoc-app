<?php

namespace App\Repository;

use App\Entity\Cvtheque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cvtheque>
 *
 * @method Cvtheque|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cvtheque|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cvtheque[]    findAll()
 * @method Cvtheque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CvthequeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cvtheque::class);
    }

    public function save(Cvtheque $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cvtheque $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function cvExist( $id_user) {

        $result = null;

        $formations="SELECT f.id from formations f WHERE f.cvtheque_id in ( SELECT c.id FROM cvtheque c WHERE c.idUser_id = ".$id_user." and c.emailPerso is not null and c.mobile is not null ) ";
        $experiences="SELECT e.id from experience e WHERE e.cvtheque_id in ( SELECT c.id FROM cvtheque c WHERE c.idUser_id = ".$id_user." and c.emailPerso is not null and c.mobile is not null ) ";

        $result_f= $this->getEntityManager()->getConnection()->prepare($formations);
        $result_e= $this->getEntityManager()->getConnection()->prepare($experiences); 

        $result_f =  $result_f->executeQuery()->fetchAllAssociative();
        $result_e =  $result_e->executeQuery()->fetchAllAssociative();

        if($result_e!=null && $result_f!=null){
            $result = "True";
        }

        return  $result ; 

    }


//    /**
//     * @return Cvtheque[] Returns an array of Cvtheque objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cvtheque
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
