<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220525131842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added Realm and RealmNode entities';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE realms (id INT AUTO_INCREMENT NOT NULL, role_id INT DEFAULT NULL, type VARCHAR(30) NOT NULL, name VARCHAR(255) NOT NULL, plain_password VARCHAR(255) DEFAULT NULL, serialization_group VARCHAR(200) DEFAULT NULL, UNIQUE INDEX UNIQ_7DF2621A5E237E06 (name), INDEX IDX_7DF2621AD60322AC (role_id), INDEX realms_type (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE realms_users (realm_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_AF42698A9DFF5F89 (realm_id), INDEX IDX_AF42698AA76ED395 (user_id), PRIMARY KEY(realm_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE realms_nodes (id INT AUTO_INCREMENT NOT NULL, node_id INT NOT NULL, realm_id INT DEFAULT NULL, inheritance_type VARCHAR(10) NOT NULL, INDEX realms_nodes_inheritance_type (inheritance_type), INDEX realms_nodes_realm (realm_id), INDEX realms_nodes_node (node_id), UNIQUE INDEX realms_nodes_unique (node_id, realm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE realms ADD CONSTRAINT FK_7DF2621AD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE realms_users ADD CONSTRAINT FK_AF42698A9DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE realms_users ADD CONSTRAINT FK_AF42698AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE realms_nodes ADD CONSTRAINT FK_A6FCC99F460D9FD7 FOREIGN KEY (node_id) REFERENCES nodes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE realms_nodes ADD CONSTRAINT FK_A6FCC99F9DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realms_users DROP FOREIGN KEY FK_AF42698A9DFF5F89');
        $this->addSql('ALTER TABLE realms_nodes DROP FOREIGN KEY FK_A6FCC99F9DFF5F89');
        $this->addSql('DROP TABLE realms');
        $this->addSql('DROP TABLE realms_users');
        $this->addSql('DROP TABLE realms_nodes');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
