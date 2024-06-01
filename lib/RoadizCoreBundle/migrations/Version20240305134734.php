<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305134734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set translations tables foreign key columns not-nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents_translations CHANGE translation_id translation_id INT NOT NULL, CHANGE document_id document_id INT NOT NULL');
        $this->addSql('ALTER TABLE folders_translations CHANGE folder_id folder_id INT NOT NULL, CHANGE translation_id translation_id INT NOT NULL');
        $this->addSql('ALTER TABLE tags_translations CHANGE tag_id tag_id INT NOT NULL, CHANGE translation_id translation_id INT NOT NULL');
        $this->addSql('ALTER TABLE tags_translations_documents CHANGE tag_translation_id tag_translation_id INT NOT NULL, CHANGE document_id document_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents_translations CHANGE translation_id translation_id INT DEFAULT NULL, CHANGE document_id document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE folders_translations CHANGE folder_id folder_id INT DEFAULT NULL, CHANGE translation_id translation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tags_translations CHANGE tag_id tag_id INT DEFAULT NULL, CHANGE translation_id translation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tags_translations_documents CHANGE tag_translation_id tag_translation_id INT DEFAULT NULL, CHANGE document_id document_id INT DEFAULT NULL');
    }
}
