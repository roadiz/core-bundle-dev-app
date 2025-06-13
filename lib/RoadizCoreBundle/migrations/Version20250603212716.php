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
        return 'Remove unused indexes on custom_forms, redirections and webhooks tables.';
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
