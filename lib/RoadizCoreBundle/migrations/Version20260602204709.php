<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602204709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixed column types and uuid column comment';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_translations CHANGE options options LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes_tags CHANGE position position DOUBLE PRECISION DEFAULT 1 NOT NULL, CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE user_log_entries CHANGE data data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE webhooks CHANGE id id BINARY(16) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_translations CHANGE options options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE nodes_tags CHANGE position position DOUBLE PRECISION DEFAULT \'1\' NOT NULL, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_log_entries CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE webhooks CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
