<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218125729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create node_type_decorator table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE node_type_decorators (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, property VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, INDEX idx_ntd_path (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE node_type_decorators');
    }
}
