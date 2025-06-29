<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629125716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[NodesSources status 1/4] Add datetime columns to nodes_sources table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources DROP created_at, DROP updated_at, DROP deleted_at');
    }
}
