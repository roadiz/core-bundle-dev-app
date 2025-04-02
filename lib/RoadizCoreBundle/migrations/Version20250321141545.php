<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321141545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_crop_alignment into node_sources_documents join table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources_documents ADD image_crop_alignment VARCHAR(12) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources_documents DROP image_crop_alignment');
    }
}
