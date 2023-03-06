<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220530112008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added RealmNode index on node and inheritanceType';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX realms_nodes_node_inheritance_type ON realms_nodes (node_id, inheritance_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX realms_nodes_node_inheritance_type ON realms_nodes');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
