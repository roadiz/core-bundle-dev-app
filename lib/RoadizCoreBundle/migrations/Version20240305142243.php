<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305142243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set composite tables foreign key columns not-nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE attribute_group_translations CHANGE attribute_group_id attribute_group_id INT NOT NULL, CHANGE translation_id translation_id INT NOT NULL');
        $this->addSql('ALTER TABLE attribute_translations CHANGE attribute_id attribute_id INT NOT NULL, CHANGE translation_id translation_id INT NOT NULL');
        $this->addSql('ALTER TABLE attribute_value_translations CHANGE translation_id translation_id INT NOT NULL, CHANGE attribute_value attribute_value INT NOT NULL');
        $this->addSql('ALTER TABLE attribute_values CHANGE attribute_id attribute_id INT NOT NULL, CHANGE node_id node_id INT NOT NULL');
        $this->addSql('ALTER TABLE attributes_documents CHANGE attribute_id attribute_id INT NOT NULL, CHANGE document_id document_id INT NOT NULL');
        $this->addSql('ALTER TABLE custom_form_answers CHANGE custom_form_id custom_form_id INT NOT NULL');
        $this->addSql('ALTER TABLE custom_form_field_attributes CHANGE custom_form_answer_id custom_form_answer_id INT NOT NULL, CHANGE custom_form_field_id custom_form_field_id INT NOT NULL');
        $this->addSql('ALTER TABLE custom_form_fields CHANGE custom_form_id custom_form_id INT NOT NULL');

        // Remove all node_type_fields where node_type_id is null before changing it to not-nullable
        $this->addSql('DELETE FROM node_type_fields WHERE node_type_id IS NULL');
        $this->addSql('ALTER TABLE node_type_fields CHANGE node_type_id node_type_id INT NOT NULL');

        // Remove all nodes where nodeType_id is null before changing it to not-nullable
        $this->addSql('DELETE FROM nodes WHERE nodeType_id IS NULL');
        $this->addSql('ALTER TABLE nodes CHANGE nodeType_id nodeType_id INT NOT NULL');

        // Remove all url_aliases where ns_id is null before changing it to not-nullable
        $this->addSql('DELETE FROM url_aliases WHERE ns_id IS NULL');
        $this->addSql('ALTER TABLE url_aliases DROP FOREIGN KEY FK_E261ED65AA2D61');
        $this->addSql('ALTER TABLE url_aliases CHANGE ns_id ns_id INT NOT NULL');
        $this->addSql('ALTER TABLE url_aliases ADD CONSTRAINT FK_E261ED65AA2D61 FOREIGN KEY (ns_id) REFERENCES nodes_sources (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE attribute_group_translations CHANGE translation_id translation_id INT DEFAULT NULL, CHANGE attribute_group_id attribute_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute_translations CHANGE translation_id translation_id INT DEFAULT NULL, CHANGE attribute_id attribute_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute_value_translations CHANGE translation_id translation_id INT DEFAULT NULL, CHANGE attribute_value attribute_value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute_values CHANGE node_id node_id INT DEFAULT NULL, CHANGE attribute_id attribute_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attributes_documents CHANGE attribute_id attribute_id INT DEFAULT NULL, CHANGE document_id document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_form_answers CHANGE custom_form_id custom_form_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_form_field_attributes CHANGE custom_form_answer_id custom_form_answer_id INT DEFAULT NULL, CHANGE custom_form_field_id custom_form_field_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_form_fields CHANGE custom_form_id custom_form_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE node_type_fields CHANGE node_type_id node_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes CHANGE nodeType_id nodeType_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE url_aliases DROP FOREIGN KEY FK_E261ED65AA2D61');
        $this->addSql('ALTER TABLE url_aliases CHANGE ns_id ns_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE url_aliases ADD CONSTRAINT FK_E261ED65AA2D61 FOREIGN KEY (ns_id) REFERENCES nodes_sources (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
