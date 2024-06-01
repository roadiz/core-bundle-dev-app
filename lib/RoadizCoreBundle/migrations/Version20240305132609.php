<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305132609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set nodes_to_nodes relation columns to not-nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_to_nodes CHANGE node_a_id node_a_id INT NOT NULL, CHANGE node_b_id node_b_id INT NOT NULL, CHANGE node_type_field_id node_type_field_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_to_nodes CHANGE node_a_id node_a_id INT DEFAULT NULL, CHANGE node_b_id node_b_id INT DEFAULT NULL, CHANGE node_type_field_id node_type_field_id INT DEFAULT NULL');
    }
}
