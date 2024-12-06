<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230713224034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial test node-types and fields';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_folder_references (page_id INT NOT NULL, folder_references_id INT NOT NULL, INDEX IDX_FB5494AAC4663E4 (page_id), INDEX IDX_FB5494AAAC7ADADE (folder_references_id), PRIMARY KEY(page_id, folder_references_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE positioned_page_user (id INT AUTO_INCREMENT NOT NULL, node_source_id INT DEFAULT NULL, user_id INT DEFAULT NULL, position DOUBLE PRECISION NOT NULL, INDEX IDX_3C43EBC78E831402 (node_source_id), INDEX IDX_3C43EBC7A76ED395 (user_id), INDEX ppu_position (position), INDEX ppu_node_source_id_position (node_source_id, position), INDEX IDX_3C43EBC7462CE4F5 (position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_folder_references ADD CONSTRAINT FK_FB5494AAC4663E4 FOREIGN KEY (page_id) REFERENCES nodes_sources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_folder_references ADD CONSTRAINT FK_FB5494AAAC7ADADE FOREIGN KEY (folder_references_id) REFERENCES folders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE positioned_page_user ADD CONSTRAINT FK_3C43EBC78E831402 FOREIGN KEY (node_source_id) REFERENCES nodes_sources (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE positioned_page_user ADD CONSTRAINT FK_3C43EBC7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents_folders DROP FOREIGN KEY FK_617BB29C162CB942');
        $this->addSql('ALTER TABLE documents_folders DROP FOREIGN KEY FK_617BB29CC33F7837');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29C162CB942 FOREIGN KEY (folder_id) REFERENCES folders (id)');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29CC33F7837 FOREIGN KEY (document_id) REFERENCES documents (id)');
        $this->addSql('ALTER TABLE nodes_sources ADD main_user_id INT DEFAULT NULL, ADD content LONGTEXT DEFAULT NULL, ADD boolean_field TINYINT(1) DEFAULT 0, ADD sub_title VARCHAR(250) DEFAULT NULL, ADD color VARCHAR(10) DEFAULT NULL, ADD over_title VARCHAR(250) DEFAULT NULL, ADD sticky TINYINT(1) DEFAULT 0, ADD stickytest TINYINT(1) DEFAULT 0, ADD amount NUMERIC(18, 3) DEFAULT NULL, ADD email_test VARCHAR(250) DEFAULT NULL, ADD settings JSON DEFAULT NULL, ADD folder VARCHAR(250) DEFAULT NULL, ADD country VARCHAR(5) DEFAULT NULL, ADD geolocation JSON DEFAULT NULL, ADD multi_geolocation JSON DEFAULT NULL, ADD layout VARCHAR(11) DEFAULT NULL, ADD link_external_url VARCHAR(250) DEFAULT NULL, ADD number INT DEFAULT NULL, ADD realm_b_secret VARCHAR(250) DEFAULT NULL, ADD realm_a_secret VARCHAR(250) DEFAULT NULL, ADD price INT DEFAULT NULL, ADD vat NUMERIC(18, 3) DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes_sources ADD CONSTRAINT FK_7C7DED6D53257A7C FOREIGN KEY (main_user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7C7DED6D53257A7C ON nodes_sources (main_user_id)');
        $this->addSql('CREATE INDEX nsapp_number ON nodes_sources (number)');
        $this->addSql('CREATE INDEX nsapp_price ON nodes_sources (price)');
        $this->addSql('CREATE INDEX nsapp_layout ON nodes_sources (layout)');
        $this->addSql('CREATE INDEX nsapp_sticky ON nodes_sources (sticky)');
        $this->addSql('CREATE INDEX nsapp_stickytest ON nodes_sources (stickytest)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_folder_references DROP FOREIGN KEY FK_FB5494AAC4663E4');
        $this->addSql('ALTER TABLE page_folder_references DROP FOREIGN KEY FK_FB5494AAAC7ADADE');
        $this->addSql('ALTER TABLE positioned_page_user DROP FOREIGN KEY FK_3C43EBC78E831402');
        $this->addSql('ALTER TABLE positioned_page_user DROP FOREIGN KEY FK_3C43EBC7A76ED395');
        $this->addSql('DROP TABLE page_folder_references');
        $this->addSql('DROP TABLE positioned_page_user');
        $this->addSql('ALTER TABLE documents_folders DROP FOREIGN KEY FK_617BB29C162CB942');
        $this->addSql('ALTER TABLE documents_folders DROP FOREIGN KEY FK_617BB29CC33F7837');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29C162CB942 FOREIGN KEY (folder_id) REFERENCES folders (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents_folders ADD CONSTRAINT FK_617BB29CC33F7837 FOREIGN KEY (document_id) REFERENCES documents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nodes_sources DROP FOREIGN KEY FK_7C7DED6D53257A7C');
        $this->addSql('DROP INDEX IDX_7C7DED6D53257A7C ON nodes_sources');
        $this->addSql('DROP INDEX nsapp_number ON nodes_sources');
        $this->addSql('DROP INDEX nsapp_price ON nodes_sources');
        $this->addSql('DROP INDEX nsapp_layout ON nodes_sources');
        $this->addSql('DROP INDEX nsapp_sticky ON nodes_sources');
        $this->addSql('DROP INDEX nsapp_stickytest ON nodes_sources');
        $this->addSql('ALTER TABLE nodes_sources DROP main_user_id, DROP content, DROP boolean_field, DROP sub_title, DROP color, DROP over_title, DROP sticky, DROP stickytest, DROP amount, DROP email_test, DROP settings, DROP folder, DROP country, DROP geolocation, DROP multi_geolocation, DROP layout, DROP link_external_url, DROP number, DROP realm_b_secret, DROP realm_a_secret, DROP price, DROP vat');
    }
}
