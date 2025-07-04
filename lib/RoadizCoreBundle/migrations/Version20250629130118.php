<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629130118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[NodesSources status 2/4] Convert nodes status to nodes_sources published_at and deleted_at.';
    }

    public function up(Schema $schema): void
    {
        $draftIds = implode(',', $this->connection->executeQuery(<<<SQL
SELECT ns.id FROM nodes_sources AS ns
    INNER JOIN nodes AS n ON n.id = ns.node_id
WHERE n.status <= 20
SQL)->fetchFirstColumn());

        $publishedIds = implode(',', $this->connection->executeQuery(<<<SQL
SELECT ns.id FROM nodes_sources AS ns
    INNER JOIN nodes AS n ON n.id = ns.node_id
WHERE n.status = 30
SQL)->fetchFirstColumn());

        $deletedIds = implode(',', $this->connection->executeQuery(<<<SQL
SELECT ns.id FROM nodes_sources AS ns
    INNER JOIN nodes AS n ON n.id = ns.node_id
WHERE n.status = 40 OR n.status = 50
SQL)->fetchFirstColumn());

        // Set nodes sources created_at and update_at from node created_at and update_at
        $this->addSql(<<<SQL
UPDATE nodes_sources,nodes SET nodes_sources.created_at = nodes.created_at, nodes_sources.updated_at = nodes.updated_at
WHERE nodes_sources.node_id=nodes.id
SQL);

        // Draft nodes sources with node status DRAFT (10, 20) and published_at is past
        $this->addSql(<<<SQL
UPDATE nodes_sources SET published_at = NULL
WHERE
    (published_at IS NOT NULL AND published_at <= NOW())
    AND id IN ({$draftIds});
SQL);

        // Publish nodes sources with node status PUBLISHED (30)
        $this->addSql(<<<SQL
UPDATE nodes_sources SET published_at = NOW()
WHERE
    (published_at IS NULL OR published_at > NOW())
    AND id IN ({$publishedIds});
SQL);

        // Delete nodes sources with node status DELETED (50) and ARCHIVED (40)
        $this->addSql(<<<SQL
UPDATE nodes_sources SET deleted_at = NOW()
WHERE
    (deleted_at IS NULL OR deleted_at > NOW())
    AND id IN ({$deletedIds});
SQL);
    }

    public function down(Schema $schema): void
    {
        // Set nodes created_at and update_at from nodes_sources created_at and update_at
        $this->addSql(<<<SQL
UPDATE nodes_sources,nodes SET nodes.created_at = nodes_sources.created_at, nodes.updated_at = nodes_sources.updated_at
WHERE nodes_sources.node_id=nodes.id
SQL);

        $draftIds = $this->connection->executeQuery(<<<SQL
SELECT n.id FROM nodes AS n
    INNER JOIN nodes_sources AS ns ON n.id = ns.node_id
WHERE ns.published_at IS NULL OR ns.published_at > NOW()
SQL)->fetchFirstColumn();

        $this->warnIf(
            count($draftIds) > 0,
            'Some nodes_sources are marked draft, this will force their node to be draft too.'
        );

        $draftIds = implode(',', $draftIds);

        $publishedIds = implode(',', $this->connection->executeQuery(<<<SQL
SELECT n.id FROM nodes AS n
    INNER JOIN nodes_sources AS ns ON n.id = ns.node_id
WHERE ns.published_at IS NOT NULL AND ns.published_at <= NOW()
SQL)->fetchFirstColumn());

        $deletedIds = $this->connection->executeQuery(<<<SQL
SELECT n.id FROM nodes AS n
    INNER JOIN nodes_sources AS ns ON n.id = ns.node_id
WHERE ns.deleted_at IS NOT NULL AND ns.deleted_at <= NOW()
SQL)->fetchFirstColumn();

        $this->warnIf(
            count($deletedIds) > 0,
            'Some nodes_sources are marked deleted, this will not be reverted!'
        );

        // Draft nodes status DRAFT (10) when nodes_sources published_at is NULL or in the future
        $this->addSql(<<<SQL
UPDATE nodes SET status = 10
WHERE id IN ({$draftIds});
SQL);

        // Draft nodes status PUBLISHED (30) when nodes_sources published_at is past
        $this->addSql(<<<SQL
UPDATE nodes SET status = 30
WHERE id IN ({$publishedIds});
SQL);
    }
}
