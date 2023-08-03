<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607134442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set font filenames to 100 chars max.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonts CHANGE eot_filename eot_filename VARCHAR(100) DEFAULT NULL, CHANGE woff_filename woff_filename VARCHAR(100) DEFAULT NULL, CHANGE woff2_filename woff2_filename VARCHAR(100) DEFAULT NULL, CHANGE otf_filename otf_filename VARCHAR(100) DEFAULT NULL, CHANGE svg_filename svg_filename VARCHAR(100) DEFAULT NULL, CHANGE name name VARCHAR(100) NOT NULL, CHANGE hash hash VARCHAR(128) NOT NULL, CHANGE folder folder VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fonts CHANGE eot_filename eot_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE woff_filename woff_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE woff2_filename woff2_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE otf_filename otf_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE svg_filename svg_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE hash hash VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE folder folder VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
