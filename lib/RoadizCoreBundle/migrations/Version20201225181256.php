<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Database initialization migration for PostgreSQL.
 *
 * @deprecated use Roadiz\Core\Migrations\Version20201203004857 instead
 */
final class Version20201225181256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[deprecated] Database initialization migration for PostgreSQL.';
    }

    public function up(Schema $schema): void
    {
        $this->write('Nothing to do with RZ\Roadiz\Migrations\Version20201225181256.');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Temporary workaround.
     *
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
