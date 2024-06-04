<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240604143759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added attributable_by_weight to node_types and color index on attributes table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_319B9E70665648E9 ON attributes (color)');
        $this->addSql('ALTER TABLE node_types ADD attributable_by_weight TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_319B9E70665648E9 ON attributes');
        $this->addSql('ALTER TABLE node_types DROP attributable_by_weight');
    }
}
