<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628170203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed user_id column type in log table to string.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('DROP INDEX IDX_8F3F68C5A76ED395 ON log');
        $this->addSql('ALTER TABLE log CHANGE user_id user_id VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8F3F68C5A76ED395 ON log (user_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
