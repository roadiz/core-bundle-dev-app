<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210133022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '⚠️ Drop node_types and node_type_fields table. Make sure you have exported the node types to file before running this migration as it is irreversible.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stack_types DROP FOREIGN KEY FK_DE24E53886D7EB51941E63B');
        $this->addSql('ALTER TABLE node_type_fields DROP FOREIGN KEY FK_1D3923596344C9E1');
        $this->addSql('DROP TABLE node_type_fields');
        $this->addSql('DROP TABLE node_types');
        $this->addSql('DROP INDEX IDX_1D3D05FC47D04729 ON nodes');
        $this->addSql('DROP INDEX node_nodetype_status_parent ON nodes');
        $this->addSql('DROP INDEX node_nodetype_status_parent_position ON nodes');
        $this->addSql('ALTER TABLE nodes DROP nodeType_id');
        $this->addSql('DROP INDEX IDX_DE24E53886D7EB51941E63B ON stack_types');
        $this->addSql('ALTER TABLE stack_types DROP nodetype_id');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Deleted node_types and node_type_fields tables cannot be recovered');
    }
}
