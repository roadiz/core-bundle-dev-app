<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305125653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set nodes_sources_documents relation columns to not-nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_sources_documents CHANGE ns_id ns_id INT NOT NULL, CHANGE document_id document_id INT NOT NULL, CHANGE node_type_field_id node_type_field_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_sources_documents CHANGE ns_id ns_id INT DEFAULT NULL, CHANGE document_id document_id INT DEFAULT NULL, CHANGE node_type_field_id node_type_field_id INT DEFAULT NULL');
    }
}
