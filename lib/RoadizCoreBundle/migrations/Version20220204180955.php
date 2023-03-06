<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220204180955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added externalUrl on DocumentTranslation and additional indexes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX document_filename ON documents (filename)');
        $this->addSql('CREATE INDEX document_embed_id ON documents (embedId)');
        $this->addSql('CREATE INDEX document_embed_platform_id ON documents (embedId, embedPlatform)');
        $this->addSql('CREATE INDEX document_duration ON documents (duration)');
        $this->addSql('CREATE INDEX document_filesize ON documents (filesize)');
        $this->addSql('CREATE INDEX document_image_width ON documents (imageWidth)');
        $this->addSql('CREATE INDEX document_image_height ON documents (imageHeight)');
        $this->addSql('ALTER TABLE documents_translations ADD external_url TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX document_filename ON documents');
        $this->addSql('DROP INDEX document_embed_id ON documents');
        $this->addSql('DROP INDEX document_embed_platform_id ON documents');
        $this->addSql('DROP INDEX document_duration ON documents');
        $this->addSql('DROP INDEX document_filesize ON documents');
        $this->addSql('DROP INDEX document_image_width ON documents');
        $this->addSql('DROP INDEX document_image_height ON documents');
        $this->addSql('ALTER TABLE documents_translations DROP external_url');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
