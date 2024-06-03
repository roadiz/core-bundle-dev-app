<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240603210209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added weight column to attributes table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attributes ADD weight INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_319B9E707CD5541 ON attributes (weight)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_319B9E707CD5541 ON attributes');
        $this->addSql('ALTER TABLE attributes DROP weight');
    }
}
