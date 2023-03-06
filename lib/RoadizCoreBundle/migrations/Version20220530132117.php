<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220530132117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added Realm behaviour';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realms ADD behaviour VARCHAR(30) DEFAULT \'none\' NOT NULL');
        $this->addSql('CREATE INDEX realms_behaviour ON realms (behaviour)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX realms_behaviour ON realms');
        $this->addSql('ALTER TABLE realms DROP behaviour');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
