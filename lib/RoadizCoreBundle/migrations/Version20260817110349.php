<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260817110349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added secret column to webhooks table for HMAC payload signing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE webhooks ADD secret VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE webhooks DROP secret');
    }
}
