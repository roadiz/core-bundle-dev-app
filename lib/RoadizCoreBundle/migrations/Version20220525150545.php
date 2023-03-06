<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220525150545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable cascade delete on RealmNodes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realms_nodes DROP FOREIGN KEY FK_A6FCC99F9DFF5F89');
        $this->addSql('ALTER TABLE realms_nodes ADD CONSTRAINT FK_A6FCC99F9DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realms_nodes DROP FOREIGN KEY FK_A6FCC99F9DFF5F89');
        $this->addSql('ALTER TABLE realms_nodes ADD CONSTRAINT FK_A6FCC99F9DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
