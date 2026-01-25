<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923153525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shadow field to nodes table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes ADD shadow TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX node_shadow ON nodes (shadow)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX node_shadow ON nodes');
        $this->addSql('ALTER TABLE nodes DROP shadow');
    }
}
