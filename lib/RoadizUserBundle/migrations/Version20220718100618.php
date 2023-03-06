<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718100618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added user_metadata table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_metadata (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, metadata JSON DEFAULT NULL, UNIQUE INDEX UNIQ_AF99D014A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_metadata ADD CONSTRAINT FK_AF99D014A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_metadata');
    }
}
