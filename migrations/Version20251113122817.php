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
        return 'Add datetime and longtext fields to nodes_sources and remove unused indexes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_3E32E39E43625D9F ON custom_forms');
        $this->addSql('DROP INDEX IDX_3E32E39E8B8E8428 ON custom_forms');
        $this->addSql('DROP INDEX IDX_7303E8FB43625D9F ON fonts');
        $this->addSql('DROP INDEX IDX_7303E8FB8B8E8428 ON fonts');
        $this->addSql('ALTER TABLE nodes_sources ADD long_text LONGTEXT DEFAULT NULL, ADD date DATETIME DEFAULT NULL, ADD datetime DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX nsapp_datetime ON nodes_sources (datetime)');
        $this->addSql('DROP INDEX IDX_3C43EBC7462CE4F5 ON positioned_page_user');
        $this->addSql('DROP INDEX IDX_1483A5E943625D9F ON users');
        $this->addSql('DROP INDEX IDX_1483A5E98B8E8428 ON users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_3E32E39E43625D9F ON custom_forms (updated_at)');
        $this->addSql('CREATE INDEX IDX_3E32E39E8B8E8428 ON custom_forms (created_at)');
        $this->addSql('CREATE INDEX IDX_7303E8FB43625D9F ON fonts (updated_at)');
        $this->addSql('CREATE INDEX IDX_7303E8FB8B8E8428 ON fonts (created_at)');
        $this->addSql('DROP INDEX nsapp_datetime ON nodes_sources');
        $this->addSql('ALTER TABLE nodes_sources DROP long_text, DROP date, DROP datetime');
        $this->addSql('CREATE INDEX IDX_3C43EBC7462CE4F5 ON positioned_page_user (position)');
        $this->addSql('CREATE INDEX IDX_1483A5E943625D9F ON users (updated_at)');
        $this->addSql('CREATE INDEX IDX_1483A5E98B8E8428 ON users (created_at)');
    }
}
