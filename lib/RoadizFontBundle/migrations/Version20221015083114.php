<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221015083114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create font table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS fonts (id INT AUTO_INCREMENT NOT NULL, variant INT NOT NULL, eot_filename VARCHAR(255) DEFAULT NULL, woff_filename VARCHAR(255) DEFAULT NULL, woff2_filename VARCHAR(255) DEFAULT NULL, otf_filename VARCHAR(255) DEFAULT NULL, svg_filename VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, folder VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7303E8FB8B8E8428 (created_at), INDEX IDX_7303E8FB43625D9F (updated_at), UNIQUE INDEX UNIQ_7303E8FB5E237E06F143BFAD (name, variant), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE fonts');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
