<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250220122840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed useless facebook_name column from users table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP facebook_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD facebook_name VARCHAR(128) DEFAULT NULL');
    }
}
