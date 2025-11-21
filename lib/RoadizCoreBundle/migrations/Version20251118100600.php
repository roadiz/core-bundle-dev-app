<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add webhook configuration columns to custom_forms table.
 */
final class Version20251118100600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add webhook configuration columns to custom_forms table for external CRM integration.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_forms ADD webhook_enabled TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE custom_forms ADD webhook_provider VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_forms ADD webhook_field_mapping JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_forms ADD webhook_extra_config JSON DEFAULT NULL');
        $this->addSql('CREATE INDEX custom_form_webhook_enabled ON custom_forms (webhook_enabled)');
        $this->addSql('CREATE INDEX custom_form_webhook_provider ON custom_forms (webhook_provider)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX custom_form_webhook_enabled ON custom_forms');
        $this->addSql('DROP INDEX custom_form_webhook_provider ON custom_forms');
        $this->addSql('ALTER TABLE custom_forms DROP webhook_enabled');
        $this->addSql('ALTER TABLE custom_forms DROP webhook_provider');
        $this->addSql('ALTER TABLE custom_forms DROP webhook_field_mapping');
        $this->addSql('ALTER TABLE custom_forms DROP webhook_extra_config');
    }
}
