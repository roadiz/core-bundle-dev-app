<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220602173719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added document file hash and algorithm';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE documents ADD file_hash VARCHAR(64) DEFAULT NULL, ADD file_hash_algorithm VARCHAR(15) DEFAULT NULL');
        $this->addSql('CREATE INDEX document_file_hash ON documents (file_hash)');
        $this->addSql('CREATE INDEX document_hash_algorithm ON documents (file_hash_algorithm)');
        $this->addSql('CREATE INDEX document_file_hash_algorithm ON documents (file_hash, file_hash_algorithm)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX document_file_hash ON documents');
        $this->addSql('DROP INDEX document_hash_algorithm ON documents');
        $this->addSql('DROP INDEX document_file_hash_algorithm ON documents');
        $this->addSql('ALTER TABLE documents DROP file_hash, DROP file_hash_algorithm');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
