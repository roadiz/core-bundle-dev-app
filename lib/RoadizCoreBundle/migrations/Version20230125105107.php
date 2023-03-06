<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230125105107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reduce NodesSources discr column length to 30 chars';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources CHANGE discr discr VARCHAR(30) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources CHANGE discr discr VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
