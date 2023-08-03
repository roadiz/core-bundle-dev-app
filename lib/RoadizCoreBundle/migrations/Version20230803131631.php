<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230803131631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop useless expired and credentials_expired fields from users table.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_1483A5E9194FED4B ON users');
        $this->addSql('ALTER TABLE users DROP expired, DROP credentials_expired');
        $this->addSql('CREATE INDEX idx_users_username ON users (username)');
        $this->addSql('CREATE INDEX idx_users_email ON users (email)');
        $this->addSql('CREATE INDEX idx_users_credentials_expires_at ON users (credentials_expires_at)');
        $this->addSql('CREATE INDEX idx_users_password_requested_at ON users (password_requested_at)');
        $this->addSql('CREATE INDEX idx_users_last_login ON users (last_login)');
        $this->addSql('CREATE INDEX idx_users_locked ON users (locked)');
        $this->addSql('CREATE INDEX IDX_1483A5E98B8E8428 ON users (created_at)');
        $this->addSql('CREATE INDEX IDX_1483A5E943625D9F ON users (updated_at)');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_1483a5e950f9bb84 TO idx_users_enabled');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_1483a5e9f9d83e2 TO idx_users_expires_at');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_1483a5e94180c698 TO idx_users_locale');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_users_username ON users');
        $this->addSql('DROP INDEX idx_users_email ON users');
        $this->addSql('DROP INDEX idx_users_credentials_expires_at ON users');
        $this->addSql('DROP INDEX idx_users_password_requested_at ON users');
        $this->addSql('DROP INDEX idx_users_last_login ON users');
        $this->addSql('DROP INDEX idx_users_locked ON users');
        $this->addSql('DROP INDEX IDX_1483A5E98B8E8428 ON users');
        $this->addSql('DROP INDEX IDX_1483A5E943625D9F ON users');
        $this->addSql('ALTER TABLE users ADD expired TINYINT(1) DEFAULT 0 NOT NULL, ADD credentials_expired TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_1483A5E9194FED4B ON users (expired)');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_users_locale TO IDX_1483A5E94180C698');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_users_enabled TO IDX_1483A5E950F9BB84');
        $this->addSql('ALTER TABLE users RENAME INDEX idx_users_expires_at TO IDX_1483A5E9F9D83E2');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
