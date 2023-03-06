<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220729100037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Switched raw document relationship to ManyToOne';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP INDEX UNIQ_A2B0728826CBD5A5, ADD INDEX IDX_A2B0728826CBD5A5 (raw_document)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE documents DROP INDEX IDX_A2B0728826CBD5A5, ADD UNIQUE INDEX UNIQ_A2B0728826CBD5A5 (raw_document)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
