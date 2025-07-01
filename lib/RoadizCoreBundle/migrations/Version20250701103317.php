<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701103317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Roles 1/3] Create JSON columns for roles in realms, usergroups and users.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE realms ADD role VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE usergroups ADD group_roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD user_roles JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE realms DROP role');
        $this->addSql('ALTER TABLE usergroups DROP group_roles');
        $this->addSql('ALTER TABLE users DROP user_roles');
    }
}
