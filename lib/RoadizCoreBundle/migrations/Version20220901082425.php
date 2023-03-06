<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220901082425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added locked and color fields to Folder';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE folders ADD locked TINYINT(1) DEFAULT \'0\' NOT NULL, ADD color VARCHAR(7) DEFAULT \'#000000\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_FE37D30FEAD2C891 ON folders (locked)');
        $this->addSql('CREATE INDEX folder_visible_position ON folders (visible, position)');
        $this->addSql('CREATE INDEX folder_parent_visible ON folders (parent_id, visible)');
        $this->addSql('CREATE INDEX folder_parent_visible_position ON folders (parent_id, visible, position)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_FE37D30FEAD2C891 ON folders');
        $this->addSql('DROP INDEX folder_visible_position ON folders');
        $this->addSql('DROP INDEX folder_parent_visible ON folders');
        $this->addSql('DROP INDEX folder_parent_visible_position ON folders');
        $this->addSql('ALTER TABLE folders DROP locked, DROP color');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
