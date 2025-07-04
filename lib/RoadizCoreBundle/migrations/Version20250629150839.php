<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629150839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[NodesSources status 3/4] Rename nodes_sources indexes to be more explicit.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX ns_created_at ON nodes_sources (created_at)');
        $this->addSql('CREATE INDEX ns_deleted_at ON nodes_sources (deleted_at)');
        $this->addSql('CREATE INDEX ns_updated_at ON nodes_sources (updated_at)');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX idx_7c7ded6d4ad26064 TO ns_discr');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX idx_7c7ded6de0d4fde1 TO ns_published_at');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX idx_7c7ded6d2b36786b TO ns_title');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX ns_created_at ON nodes_sources');
        $this->addSql('DROP INDEX ns_deleted_at ON nodes_sources');
        $this->addSql('DROP INDEX ns_updated_at ON nodes_sources');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX ns_discr TO IDX_7C7DED6D4AD26064');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX ns_title TO IDX_7C7DED6D2B36786B');
        $this->addSql('ALTER TABLE nodes_sources RENAME INDEX ns_published_at TO IDX_7C7DED6DE0D4FDE1');
    }
}
