<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124133946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add column and populate nodetype_name into nodes and stack_types';
    }

    public function up(Schema $schema): void
    {
        // UPDATE Table nodes with new column nodetype_name
        $this->addSql('ALTER TABLE nodes ADD nodetype_name VARCHAR(30) NOT NULL');
        // Keep nodeType_id be make it nullable
        $this->addSql('ALTER TABLE nodes CHANGE nodeType_id nodeType_id INT DEFAULT NULL');
        // Remove foreign key on NodeType table (node-types will be static files)
        $this->addSql('ALTER TABLE nodes DROP FOREIGN KEY FK_1D3D05FC47D04729');
        // Migrate data for populate nodetype_name
        $this->addSql('UPDATE nodes INNER JOIN node_types ON nodes.nodeType_id = node_types.id SET nodes.nodetype_name = node_types.name');
        // Add Index
        $this->addSql('CREATE INDEX node_ntname ON nodes (nodetype_name)');
        // UPDATE stack_type with new column nodetype_name
        $this->addSql('ALTER TABLE stack_types ADD nodetype_name VARCHAR(30) NOT NULL');
        // Migrate data to populate nodetype_name instead of nodeType_id
        $this->addSql('UPDATE stack_types INNER JOIN node_types ON stack_types.nodeType_id = node_types.id SET stack_types.nodetype_name = node_types.name');
        // Drop and add constraint and Index on stack_types table
        $this->addSql('ALTER TABLE stack_types DROP FOREIGN KEY FK_DE24E53886D7EB5');
        $this->addSql('DROP INDEX IDX_DE24E53886D7EB5 ON stack_types');
        $this->addSql('DROP INDEX `primary` ON stack_types');
        $this->addSql('ALTER TABLE stack_types ADD CONSTRAINT FK_DE24E53886D7EB51941E63B FOREIGN KEY (nodetype_name) REFERENCES node_types (name) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DE24E53886D7EB51941E63B ON stack_types (nodetype_name)');
        $this->addSql('ALTER TABLE stack_types ADD PRIMARY KEY (node_id, nodetype_name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes CHANGE nodeType_id nodeType_id INT NOT NULL');
        $this->addSql('ALTER TABLE nodes ADD CONSTRAINT FK_1D3D05FC47D04729 FOREIGN KEY (nodeType_id) REFERENCES node_types (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX node_ntname ON nodes');
        $this->addSql('ALTER TABLE nodes DROP nodetype_name');
        $this->addSql('ALTER TABLE stack_types DROP FOREIGN KEY FK_DE24E53886D7EB51941E63B');
        $this->addSql('DROP INDEX IDX_DE24E53886D7EB51941E63B ON stack_types');
        $this->addSql('DROP INDEX `PRIMARY` ON stack_types');
        $this->addSql('ALTER TABLE stack_types DROP nodetype_name');
        $this->addSql('ALTER TABLE stack_types ADD CONSTRAINT FK_DE24E53886D7EB5 FOREIGN KEY (nodeType_id) REFERENCES node_types (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DE24E53886D7EB5 ON stack_types (nodeType_id)');
        $this->addSql('ALTER TABLE stack_types ADD PRIMARY KEY (node_id, nodeType_id)');
    }
}
