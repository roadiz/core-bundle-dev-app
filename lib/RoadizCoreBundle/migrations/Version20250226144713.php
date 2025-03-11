<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226144713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reduce Documents folder column length and changed settings type';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents CHANGE folder folder VARCHAR(12) DEFAULT NULL');
        $this->addSql('ALTER TABLE settings CHANGE type type SMALLINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents CHANGE folder folder VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE type type SMALLINT NOT NULL');
    }
}
