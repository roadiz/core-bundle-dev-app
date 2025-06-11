<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603212716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused indexes on custom_forms, fonts, positioned_page_user, redirections and webhooks tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3E32E39E8B8E8428 ON custom_forms
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3E32E39E43625D9F ON custom_forms
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7303E8FB8B8E8428 ON fonts
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7303E8FB43625D9F ON fonts
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3C43EBC7462CE4F5 ON positioned_page_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_38F5ECE48B8E8428 ON redirections
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_38F5ECE443625D9F ON redirections
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_998C4FDD8B8E8428 ON webhooks
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_998C4FDD43625D9F ON webhooks
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3E32E39E8B8E8428 ON custom_forms (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3E32E39E43625D9F ON custom_forms (updated_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7303E8FB8B8E8428 ON fonts (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7303E8FB43625D9F ON fonts (updated_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C43EBC7462CE4F5 ON positioned_page_user (position)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_38F5ECE48B8E8428 ON redirections (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_38F5ECE443625D9F ON redirections (updated_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_998C4FDD8B8E8428 ON webhooks (created_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_998C4FDD43625D9F ON webhooks (updated_at)
        SQL);
    }
}
