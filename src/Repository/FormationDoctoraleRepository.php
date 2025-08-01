<?php

namespace App\Repository;

use App\Entity\FormationDoctorale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormationDoctorale>
 *
 * @method FormationDoctorale|null find($id, $lockMode = null, $lockVersion = null)
 * @method FormationDoctorale|null findOneBy(array $criteria, array $orderBy = null)
 * @method FormationDoctorale[]    findAll()
 * @method FormationDoctorale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationDoctoraleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormationDoctorale::class);
    }
}