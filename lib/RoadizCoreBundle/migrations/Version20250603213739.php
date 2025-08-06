<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603213739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed webhooks UUID column to native type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks ADD id_bin BINARY(16) NULL COMMENT '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE webhooks SET id_bin = UUID_TO_BIN(id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks DROP PRIMARY KEY
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks DROP COLUMN id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks CHANGE id_bin id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks ADD PRIMARY KEY (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks ADD id_char VARCHAR(36) NULL
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE webhooks SET id_char = BIN_TO_UUID(id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks DROP PRIMARY KEY
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks DROP COLUMN id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks CHANGE id_char id VARCHAR(36) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE webhooks ADD PRIMARY KEY (id)
        SQL);
    }
}
