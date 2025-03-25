<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325092319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add hotspot for documents and nodes_sources_documents';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents ADD hotspot JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes_sources_documents ADD hotspot JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP hotspot');
        $this->addSql('ALTER TABLE nodes_sources_documents DROP hotspot');
    }
}
