<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add built-in unpublished_at datetime field on nodes_sources to schedule content expiration.
 *
 * Symmetrical to published_at: content is publicly visible only when
 * published_at <= now AND (unpublished_at > now OR unpublished_at IS NULL).
 *
 * BC NOTE: this migration is guarded so it is a no-op on projects that already own an
 * unpublished_at column on nodes_sources (e.g. added by a project-level migration or by a
 * custom node-type field). Projects that previously declared a custom "unpublished_at"
 * node-type field must remove that field from their node-type definitions, regenerate their
 * entities and add a project migration copying the legacy per-type values into
 * nodes_sources.unpublished_at before dropping the old per-type column.
 */
final class Version20260713120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add built-in unpublished_at field and indexes on nodes_sources for scheduled content expiration.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('nodes_sources');

        if (!$table->hasColumn('unpublished_at')) {
            $this->addSql('ALTER TABLE nodes_sources ADD unpublished_at DATETIME DEFAULT NULL');
        }
        if ($table->hasIndex('nsapp_unpublished_at')) {
            $this->addSql('DROP INDEX nsapp_unpublished_at ON nodes_sources');
        }
        $this->addSql('CREATE INDEX ns_unpublished_at ON nodes_sources (unpublished_at)');

        if (!$table->hasIndex('ns_node_translation_unpublished')) {
            $this->addSql('CREATE INDEX ns_node_translation_unpublished ON nodes_sources (node_id, translation_id, unpublished_at)');
        }
        if (!$table->hasIndex('ns_node_discr_translation_unpublished')) {
            $this->addSql('CREATE INDEX ns_node_discr_translation_unpublished ON nodes_sources (node_id, discr, translation_id, unpublished_at)');
        }
        if (!$table->hasIndex('ns_discr_translation_unpublished')) {
            $this->addSql('CREATE INDEX ns_discr_translation_unpublished ON nodes_sources (discr, translation_id, unpublished_at)');
        }
        if (!$table->hasIndex('ns_title_translation_unpublished')) {
            $this->addSql('CREATE INDEX ns_title_translation_unpublished ON nodes_sources (title, translation_id, unpublished_at)');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX ns_unpublished_at ON nodes_sources');
        $this->addSql('DROP INDEX ns_node_translation_unpublished ON nodes_sources');
        $this->addSql('DROP INDEX ns_node_discr_translation_unpublished ON nodes_sources');
        $this->addSql('DROP INDEX ns_discr_translation_unpublished ON nodes_sources');
        $this->addSql('DROP INDEX ns_title_translation_unpublished ON nodes_sources');
        $this->addSql('ALTER TABLE nodes_sources DROP unpublished_at');
    }
}
