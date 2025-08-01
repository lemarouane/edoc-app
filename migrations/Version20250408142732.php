<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Add statut column to reinscription_details table
 */
final class Version20250731122100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add statut column to reinscription_details table';
    }

    public function up(Schema $schema): void
    {
        // Add statut column with default value 'En cours'
        $this->addSql('ALTER TABLE reinscription_details ADD statut VARCHAR(50) NOT NULL DEFAULT "En cours"');
        // Update existing records to 'En cours' (redundant due to DEFAULT, but ensures consistency)
        $this->addSql('UPDATE reinscription_details SET statut = "En cours" WHERE statut IS NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove statut column
        $this->addSql('ALTER TABLE reinscription_details DROP statut');
    }
}