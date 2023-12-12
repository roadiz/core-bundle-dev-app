<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231013132932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added node-type attributable field';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE node_types ADD attributable TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_409B1BCC1F470BBD ON node_types (attributable)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_409B1BCC1F470BBD ON node_types');
        $this->addSql('ALTER TABLE node_types DROP attributable');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
