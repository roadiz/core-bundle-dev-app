<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230905140844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add realm to attribute and attribute_value';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_values ADD realm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute_values ADD CONSTRAINT FK_184662BC9DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_184662BC9DFF5F89 ON attribute_values (realm_id)');
        $this->addSql('ALTER TABLE attributes ADD realm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attributes ADD CONSTRAINT FK_319B9E709DFF5F89 FOREIGN KEY (realm_id) REFERENCES realms (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_319B9E709DFF5F89 ON attributes (realm_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_values DROP FOREIGN KEY FK_184662BC9DFF5F89');
        $this->addSql('DROP INDEX IDX_184662BC9DFF5F89 ON attribute_values');
        $this->addSql('ALTER TABLE attribute_values DROP realm_id');
        $this->addSql('ALTER TABLE attributes DROP FOREIGN KEY FK_319B9E709DFF5F89');
        $this->addSql('DROP INDEX IDX_319B9E709DFF5F89 ON attributes');
        $this->addSql('ALTER TABLE attributes DROP realm_id');
    }
}
