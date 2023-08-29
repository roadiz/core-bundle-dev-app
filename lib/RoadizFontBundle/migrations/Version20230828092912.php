<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230828092912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixed Font inherited indexes.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX font_created_at ON fonts (created_at)');
        $this->addSql('CREATE INDEX font_updated_at ON fonts (updated_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX font_created_at ON fonts');
        $this->addSql('DROP INDEX font_updated_at ON fonts');
    }
}
