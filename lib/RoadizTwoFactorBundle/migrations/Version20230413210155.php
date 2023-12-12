<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413210155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added activated_at column to TwoFactorUser entity to prevent TOTP code request if user never activated first time';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE two_factor_users ADD activated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE two_factor_users DROP activated_at');
    }
}
