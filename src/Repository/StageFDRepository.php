<?php

namespace App\Repository;

use App\Entity\StageFD;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StageFD>
 *
 * @method StageFD|null find($id, $lockMode = null, $lockVersion = null)
 * @method StageFD|null findOneBy(array $criteria, array $orderBy = null)
 * @method StageFD[]    findAll()
 * @method StageFD[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StageFDRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StageFD::class);
    }

    public function save(StageFD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StageFD $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find all stages for a specific doctorant, ordered by ID descending
     */
    public function findAllByDoctorantId(int $doctorantId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.doctorantId = :doctorantId')
            ->setParameter('doctorantId', $doctorantId)
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the latest stage for a specific doctorant
     */
    public function findLatestByDoctorantId(int $doctorantId): ?StageFD
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.doctorantId = :doctorantId')
            ->setParameter('doctorantId', $doctorantId)
            ->orderBy('s.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find stages by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stages by module ID
     */
    public function findByModuleId(int $moduleId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.moduleId = :moduleId')
            ->setParameter('moduleId', $moduleId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stages within a date range
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateDebut >= :startDate')
            ->andWhere('s.dateFin <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stages that are currently active (between start and end dates)
     */
    public function findActiveStages(): array
    {
        $now = new \DateTime();
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateDebut <= :now')
            ->andWhere('s.dateFin >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stages by status and doctorant ID
     */
    public function findByStatusAndDoctorantId(string $status, int $doctorantId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.doctorantId = :doctorantId')
            ->setParameter('status', $status)
            ->setParameter('doctorantId', $doctorantId)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return StageFD[] Returns an array of StageFD objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StageFD
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
