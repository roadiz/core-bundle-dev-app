<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240318184556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change node-type fields ID to their names in nodes_custom_forms, nodes_sources_documents and nodes_to_nodes tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_custom_forms DROP FOREIGN KEY FK_4D401A0C47705282');
        $this->addSql('DROP INDEX IDX_4D401A0C47705282 ON nodes_custom_forms');
        $this->addSql('DROP INDEX customform_node_field_position ON nodes_custom_forms');
        $this->addSql('CREATE INDEX customform_node_field_position ON nodes_custom_forms (node_id, field_name, position)');
        $this->addSql('ALTER TABLE nodes_sources_documents DROP FOREIGN KEY FK_1CD104F747705282');
        $this->addSql('DROP INDEX IDX_1CD104F747705282 ON nodes_sources_documents');
        $this->addSql('DROP INDEX nsdoc_field ON nodes_sources_documents');
        $this->addSql('DROP INDEX nsdoc_field_position ON nodes_sources_documents');
        $this->addSql('CREATE INDEX nsdoc_field ON nodes_sources_documents (ns_id, field_name)');
        $this->addSql('CREATE INDEX nsdoc_field_position ON nodes_sources_documents (ns_id, field_name, position)');
        $this->addSql('ALTER TABLE nodes_to_nodes DROP FOREIGN KEY FK_761F9A9147705282');
        $this->addSql('DROP INDEX IDX_761F9A9147705282 ON nodes_to_nodes');
        $this->addSql('DROP INDEX node_a_field ON nodes_to_nodes');
        $this->addSql('DROP INDEX node_a_field_position ON nodes_to_nodes');
        $this->addSql('DROP INDEX node_b_field ON nodes_to_nodes');
        $this->addSql('DROP INDEX node_b_field_position ON nodes_to_nodes');
        $this->addSql('CREATE INDEX node_a_field ON nodes_to_nodes (node_a_id, field_name)');
        $this->addSql('CREATE INDEX node_a_field_position ON nodes_to_nodes (node_a_id, field_name, position)');
        $this->addSql('CREATE INDEX node_b_field ON nodes_to_nodes (node_b_id, field_name)');
        $this->addSql('CREATE INDEX node_b_field_position ON nodes_to_nodes (node_b_id, field_name, position)');

        /*
         * DESTRUCTIVE OPERATIONS
         */
        $this->addSql('ALTER TABLE nodes_custom_forms CHANGE field_name field_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE nodes_sources_documents CHANGE field_name field_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE nodes_to_nodes CHANGE field_name field_name VARCHAR(250) NOT NULL');

        $this->addSql('ALTER TABLE nodes_custom_forms DROP node_type_field_id');
        $this->addSql('ALTER TABLE nodes_sources_documents DROP node_type_field_id');
        $this->addSql('ALTER TABLE nodes_to_nodes DROP node_type_field_id');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cannot convert node-type fields name back to their identifiers');
    }
}
