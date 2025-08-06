<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519152532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added css, yaml, and json columns to nodes_sources table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE nodes_sources ADD css LONGTEXT DEFAULT NULL, ADD yaml LONGTEXT DEFAULT NULL, ADD json LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE nodes_sources DROP css, DROP yaml, DROP json
        SQL);
    }
}
