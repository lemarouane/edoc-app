<?php

namespace App\Repository;

use App\Entity\ConventionDD;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConventionDD>
 *
 * @method ConventionDD|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConventionDD|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConventionDD[]    findAll()
 * @method ConventionDD[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConventionDDRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConventionDD::class);
    }

    public function save(ConventionDD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConventionDD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function searchConventionDDByCodes($codes,$cn) {

        $query="SELECT * FROM convention_dd c WHERE (";
        foreach($codes as $code){
            $list = explode("_",$code);  
            $query .= "  c.filiere like '%".$list[1]."%' OR";
                
        }
        $query=substr($query, 0, -2);
        $query .=" )";
        $result= $cn->fetchAllAssociative($query);
        return  $result ; 
    
    }
    

//    /**
//     * @return ConventionDD[] Returns an array of ConventionDD objects
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

//    public function findOneBySomeField($value): ?ConventionDD
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
