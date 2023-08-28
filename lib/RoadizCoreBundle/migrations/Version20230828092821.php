<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230828092821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixed inherited indexes.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX custom_form_created_at ON custom_forms (created_at)');
        $this->addSql('CREATE INDEX custom_form_updated_at ON custom_forms (updated_at)');
        $this->addSql('CREATE INDEX redirection_created_at ON redirections (created_at)');
        $this->addSql('CREATE INDEX redirection_updated_at ON redirections (updated_at)');
        $this->addSql('CREATE INDEX idx_user_created_at ON users (created_at)');
        $this->addSql('CREATE INDEX idx_user_updated_at ON users (updated_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX custom_form_created_at ON custom_forms');
        $this->addSql('DROP INDEX custom_form_updated_at ON custom_forms');
        $this->addSql('DROP INDEX redirection_created_at ON redirections');
        $this->addSql('DROP INDEX redirection_updated_at ON redirections');
        $this->addSql('DROP INDEX idx_user_created_at ON users');
        $this->addSql('DROP INDEX idx_user_updated_at ON users');
    }
}
