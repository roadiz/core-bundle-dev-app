<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230804153629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create a single UUID field primary key on nodes_tags table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `primary` ON nodes_tags');
        $this->addSql('ALTER TABLE nodes_tags ADD id VARCHAR(36) DEFAULT NULL');
        $this->addSql('UPDATE nodes_tags SET id = UUID() WHERE id IS NULL');
        $this->addSql('ALTER TABLE nodes_tags CHANGE id id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE nodes_tags ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `PRIMARY` ON nodes_tags');
        $this->addSql('ALTER TABLE nodes_tags DROP id');
        $this->addSql('ALTER TABLE nodes_tags ADD PRIMARY KEY (node_id, tag_id, position)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
