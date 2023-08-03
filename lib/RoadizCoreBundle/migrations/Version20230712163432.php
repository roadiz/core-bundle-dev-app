<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230712163432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove relation between UserLogEntry and User (to separate entity managers)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_log_entries DROP FOREIGN KEY FK_BC2E42C7A76ED395');
        $this->addSql('DROP INDEX IDX_BC2E42C7A76ED395 ON user_log_entries');
        $this->addSql('ALTER TABLE user_log_entries DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_log_entries ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_log_entries ADD CONSTRAINT FK_BC2E42C7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_BC2E42C7A76ED395 ON user_log_entries (user_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
