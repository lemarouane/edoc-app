<?php

namespace App\Repository;

use App\Entity\EtuDiplomeCarte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EtuDiplomeCarte>
 *
 * @method EtuDiplomeCarte|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtuDiplomeCarte|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtuDiplomeCarte[]    findAll()
 * @method EtuDiplomeCarte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtuDiplomeCarteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtuDiplomeCarte::class);
    }

    public function save(EtuDiplomeCarte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EtuDiplomeCarte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function rechercheByDecision($code,$value,$type) {
       
        $query = $this->createQueryBuilder('d');
        $query->andWhere('d.codeEtudiant = :code')->setParameter('code', $code);
        $query->andWhere('d.type = :type')->setParameter('type', $type);
        $query->andWhere('d.valueType = :value')->setParameter('value', $value);
        $query->andWhere('d.decision = :decision')->setParameter('decision', '0');
        return $query->getQuery()->getResult(); 
    }
	public function rechercheByDecision1($code,$value,$type) {
       
        $query = $this->createQueryBuilder('d');
        $query->andWhere('d.codeEtudiant = :code')->setParameter('code', $code);
        $query->andWhere('d.type = :type')->setParameter('type', $type);
        $query->andWhere('d.valueType = :value')->setParameter('value', $value);
        $query->andWhere('d.decision != :decision')->setParameter('decision', '0');
        return $query->getQuery()->getResult(); 
    }

//    /**
//     * @return EtuDiplomeCarte[] Returns an array of EtuDiplomeCarte objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EtuDiplomeCarte
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
