<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240318204224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename field_name length to 50 characters maximum.';
    }

    public function up(Schema $schema): void
    {
        $result = $this->connection->executeQuery('SELECT max(length(name)) FROM `node_type_fields`');
        $maxLength = $result->fetchOne();

        $this->skipIf(!is_numeric($maxLength), 'Cannot find node_type_fields name maximum length.');
        $this->skipIf($maxLength >= 50, 'You have at least on node_type_field name that exceed 50 characters long.');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE node_type_fields CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX ntf_name ON node_type_fields (name)');
        $this->addSql('ALTER TABLE nodes_custom_forms CHANGE field_name field_name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE nodes_sources_documents CHANGE field_name field_name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE nodes_to_nodes CHANGE field_name field_name VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX ntf_name ON node_type_fields');
        $this->addSql('ALTER TABLE node_type_fields CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE nodes_custom_forms CHANGE field_name field_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE nodes_sources_documents CHANGE field_name field_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE nodes_to_nodes CHANGE field_name field_name VARCHAR(250) NOT NULL');
    }
}
