<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class DoctorantRepository
{
    private $connection;
    private $logger;

    public function __construct(EntityManagerInterface $customerEm, LoggerInterface $logger)
    {
        $this->connection = $customerEm->getConnection();
        $this->logger = $logger;

        // Debug: Log the database name
        $databaseName = $this->connection->getDatabase();
        $this->logger->info("DoctorantRepository connected to database: $databaseName");
    }

    public function findDoctorantByCin(string $cin): ?array
    {
        $this->logger->info("Executing findDoctorantByCin with CIN: $cin");
        $stmt = $this->connection->prepare("
            SELECT d.*, vd.id as validated_id, vd.structure_id, vd.personnel_id
            FROM doctorants d
            JOIN validated_doctorants vd ON vd.doctorant_id = d.id
            WHERE d.cin = :cin
        ");
        return $stmt->executeQuery(['cin' => $cin])->fetchAssociative() ?: null;
    }

    public function findStructureById(int $structureId): ?array
    {
        $stmt = $this->connection->prepare("
            SELECT libelle_structure 
            FROM struct_rech 
            WHERE id = :structure_id
        ");
        return $stmt->executeQuery(['structure_id' => $structureId])->fetchAssociative() ?: null;
    }

    public function findDirecteurTheseByPersonnelId(int $personnelId): ?array
    {
        $stmt = $this->connection->prepare("
            SELECT nom, prenom
            FROM personnel
            WHERE id = :personnel_id
        ");
        return $stmt->executeQuery(['personnel_id' => $personnelId])->fetchAssociative() ?: null;
    }

    public function findResponsableStructureByStructureId(?int $structureId): ?array
    {
        if (!$structureId) {
            return null;
        }

        $stmt = $this->connection->prepare("
            SELECT u.id 
            FROM utilisateurs u
            WHERE JSON_CONTAINS(u.codes, :structure_code)
        ");
        $utilisateur = $stmt->executeQuery(['structure_code' => '"STR_' . $structureId . '"'])->fetchAssociative();

        if (!$utilisateur) {
            return null;
        }

        $stmt = $this->connection->prepare("
            SELECT p.nom, p.prenom
            FROM personnel p
            WHERE p.id_user_id = :user_id
        ");
        return $stmt->executeQuery(['user_id' => $utilisateur['id']])->fetchAssociative() ?: null;
    }
}