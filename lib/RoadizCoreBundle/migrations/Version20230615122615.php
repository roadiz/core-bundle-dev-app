<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230615122615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added Redirection use count.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE redirections ADD use_count INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX redirection_use_count ON redirections (use_count)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX redirection_use_count ON redirections');
        $this->addSql('ALTER TABLE redirections DROP use_count');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
