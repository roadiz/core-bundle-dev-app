<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240305133641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set nodes_sources node_id and translation_id columns not-nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_sources CHANGE node_id node_id INT NOT NULL, CHANGE translation_id translation_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nodes_sources CHANGE node_id node_id INT DEFAULT NULL, CHANGE translation_id translation_id INT DEFAULT NULL');
    }
}
