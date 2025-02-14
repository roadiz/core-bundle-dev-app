<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213093448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes NULL published_at dates in nodes_sources';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE nodes_sources SET published_at = "2025-02-13 09:34:48" WHERE published_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // Do nothing
    }
}
