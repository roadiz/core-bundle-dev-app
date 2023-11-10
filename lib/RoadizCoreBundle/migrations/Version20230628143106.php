<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628143106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactored log table to store entity class and id instead of node source id.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE log ADD entity_class VARCHAR(255) DEFAULT NULL, ADD entity_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_8F3F68C541BF2C66 ON log (entity_class)');
        $this->addSql('CREATE INDEX IDX_8F3F68C541BF2C6681257D5D ON log (entity_class, entity_id)');
        $this->addSql('CREATE INDEX log_entity_class_datetime ON log (entity_class, datetime)');
        $this->addSql('CREATE INDEX log_entity_class_id_datetime ON log (entity_class, entity_id, datetime)');

        // Move node_source_id to entity_class and entity_id
        $nodeSourceClass = NodesSources::class;
        $this->addSql(<<<EOF
UPDATE log
SET log.entity_class = '{$nodeSourceClass}',
    log.entity_id = log.node_source_id
WHERE log.node_source_id IS NOT NULL
EOF
        );

        // Drop old indexes and foreign key on node_source_id
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C58E831402');
        $this->addSql('ALTER TABLE log DROP node_source_id');
        $this->addSql('DROP INDEX log_ns_datetime ON log');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE log ADD node_source_id INT DEFAULT NULL');

        // Move entity_class and entity_id to node_source_id
        $nodeSourceClass = NodesSources::class;
        $this->addSql(<<<EOF
UPDATE log
SET log.node_source_id = log.entity_id
WHERE log.entity_class = '{$nodeSourceClass}'
EOF
        );

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_8F3F68C541BF2C66 ON log');
        $this->addSql('DROP INDEX IDX_8F3F68C541BF2C6681257D5D ON log');
        $this->addSql('DROP INDEX log_entity_class_datetime ON log');
        $this->addSql('DROP INDEX log_entity_class_id_datetime ON log');
        $this->addSql('ALTER TABLE log DROP entity_class, DROP entity_id');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C58E831402 FOREIGN KEY (node_source_id) REFERENCES nodes_sources (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX log_ns_datetime ON log (node_source_id, datetime)');
        $this->addSql('CREATE INDEX IDX_8F3F68C58E831402 ON log (node_source_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
