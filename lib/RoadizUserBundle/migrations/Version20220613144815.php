<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220613144815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added User validation token table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_validation_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, token_valid_until DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D613F87E5F37A13B (token), INDEX IDX_D613F87EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_validation_tokens ADD CONSTRAINT FK_D613F87EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_validation_tokens');
    }
}
