<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230829082257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Indexes for attribute_values table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_attribute_value_node_position ON attribute_values (node_id, position)');
        $this->addSql('CREATE INDEX idx_attribute_value_position ON attribute_values (position)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_attribute_value_node_position ON attribute_values');
        $this->addSql('DROP INDEX idx_attribute_value_position ON attribute_values');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
