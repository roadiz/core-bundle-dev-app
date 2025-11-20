<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113122817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add datetime and longtext fields to nodes_sources';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nodes_sources ADD long_text LONGTEXT DEFAULT NULL, ADD date DATETIME DEFAULT NULL, ADD datetime DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX nsapp_datetime ON nodes_sources (datetime)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX nsapp_datetime ON nodes_sources');
        $this->addSql('ALTER TABLE nodes_sources DROP long_text, DROP date, DROP datetime');
    }
}
