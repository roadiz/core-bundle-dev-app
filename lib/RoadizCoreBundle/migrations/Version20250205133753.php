<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205133753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added new indexes on nodes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX node_ntname_status ON nodes (nodetype_name, status)');
        $this->addSql('CREATE INDEX node_ntname_status_parent ON nodes (nodetype_name, status, parent_node_id)');
        $this->addSql('CREATE INDEX node_ntname_status_parent_position ON nodes (nodetype_name, status, parent_node_id, position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX node_ntname_status ON nodes');
        $this->addSql('DROP INDEX node_ntname_status_parent ON nodes');
        $this->addSql('DROP INDEX node_ntname_status_parent_position ON nodes');
    }
}
