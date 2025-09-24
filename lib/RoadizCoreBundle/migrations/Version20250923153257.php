<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923153257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop useless sterile field from nodes table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_1D3D05FCF32D8BE6 ON nodes');
        $this->addSql('ALTER TABLE nodes DROP sterile');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes ADD sterile TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_1D3D05FCF32D8BE6 ON nodes (sterile)');
    }
}
