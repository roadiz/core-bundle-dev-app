<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220623133037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added Document copyright dates fields';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents ADD copyright_valid_since DATETIME DEFAULT NULL, ADD copyright_valid_until DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP copyright_valid_since, DROP copyright_valid_until');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
