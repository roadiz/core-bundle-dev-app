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
        $this->addSql('DROP INDEX IDX_7303E8FB8B8E8428 ON fonts');
        $this->addSql('DROP INDEX IDX_7303E8FB43625D9F ON fonts');
        $this->addSql('CREATE INDEX font_created_at ON fonts (created_at)');
        $this->addSql('CREATE INDEX font_updated_at ON fonts (updated_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX font_created_at ON fonts');
        $this->addSql('DROP INDEX font_updated_at ON fonts');
        $this->addSql('CREATE INDEX IDX_7303E8FB8B8E8428 ON fonts (created_at)');
        $this->addSql('CREATE INDEX IDX_7303E8FB43625D9F ON fonts (updated_at)');
    }
}
