<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413154052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added TwoFactorUser entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE two_factor_users (user_id INT NOT NULL, secret VARCHAR(255) DEFAULT NULL, backup_codes JSON DEFAULT NULL, trusted_version INT DEFAULT 1 NOT NULL, algorithm VARCHAR(6) DEFAULT NULL, period SMALLINT DEFAULT NULL, digits SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_12ED8E9FA76ED395 (user_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE two_factor_users ADD CONSTRAINT FK_12ED8E9FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE two_factor_users');
    }
}
