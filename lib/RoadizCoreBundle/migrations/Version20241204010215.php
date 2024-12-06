<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204010215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reduce type column size to SMALLINT';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_form_fields CHANGE type type SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE documents CHANGE imageWidth imageWidth SMALLINT DEFAULT 0 NOT NULL, CHANGE imageHeight imageHeight SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE node_type_fields CHANGE type type SMALLINT DEFAULT 0 NOT NULL, CHANGE min_length min_length SMALLINT DEFAULT NULL, CHANGE max_length max_length SMALLINT DEFAULT NULL, CHANGE serialization_max_depth serialization_max_depth SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes CHANGE status status SMALLINT DEFAULT 10 NOT NULL');
        $this->addSql('ALTER TABLE redirections CHANGE type type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE type type SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_form_fields CHANGE type type INT NOT NULL');
        $this->addSql('ALTER TABLE documents CHANGE imageWidth imageWidth INT DEFAULT 0 NOT NULL, CHANGE imageHeight imageHeight INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE node_type_fields CHANGE serialization_max_depth serialization_max_depth INT DEFAULT NULL, CHANGE min_length min_length INT DEFAULT NULL, CHANGE max_length max_length INT DEFAULT NULL, CHANGE type type INT NOT NULL');
        $this->addSql('ALTER TABLE nodes CHANGE status status INT NOT NULL');
        $this->addSql('ALTER TABLE redirections CHANGE type type INT NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE type type INT NOT NULL');
    }
}
