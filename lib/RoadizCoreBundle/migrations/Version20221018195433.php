<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018195433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Transform nodes_tags relation with position attribute';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_tags DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE nodes_tags ADD position DOUBLE PRECISION DEFAULT \'1\' NOT NULL');
        $this->addSql('CREATE INDEX nodes_tags_node_id_position ON nodes_tags (node_id, position)');
        $this->addSql('CREATE INDEX nodes_tags_tag_id_position ON nodes_tags (tag_id, position)');
        $this->addSql('CREATE INDEX nodes_tags_position ON nodes_tags (position)');
        $this->addSql('ALTER TABLE nodes_tags ADD PRIMARY KEY (node_id, tag_id, position)');
        $this->addSql('ALTER TABLE nodes_tags RENAME INDEX idx_5b5cb38cbad26311 TO nodes_tags_tag_id');
        $this->addSql('ALTER TABLE nodes_tags RENAME INDEX idx_5b5cb38c460d9fd7 TO nodes_tags_node_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX nodes_tags_node_id_position ON nodes_tags');
        $this->addSql('DROP INDEX nodes_tags_tag_id_position ON nodes_tags');
        $this->addSql('DROP INDEX nodes_tags_position ON nodes_tags');
        $this->addSql('ALTER TABLE nodes_tags DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE nodes_tags DROP position');
        $this->addSql('ALTER TABLE nodes_tags ADD PRIMARY KEY (node_id, tag_id)');
        $this->addSql('ALTER TABLE nodes_tags RENAME INDEX nodes_tags_node_id TO IDX_5B5CB38C460D9FD7');
        $this->addSql('ALTER TABLE nodes_tags RENAME INDEX nodes_tags_tag_id TO IDX_5B5CB38CBAD26311');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
