<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Convert user_log_entries.data from PHP-serialized (legacy DBAL "array" type) to JSON.
 *
 * Must run BEFORE Version20260602204709 which alters the column to a native JSON type:
 * the ALTER would otherwise fail on every row still holding a PHP-serialized string.
 */
final class Version20260602204708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert user_log_entries.data from PHP-serialized to JSON before switching the column to JSON type.';
    }

    public function up(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(
            // Skip rows that already look like JSON ([...] or {...}); only legacy serialized payloads remain.
            "SELECT id, data FROM user_log_entries WHERE data IS NOT NULL AND data <> '' AND data NOT LIKE '[%' AND data NOT LIKE '{%'"
        );

        foreach ($rows as $row) {
            $value = @unserialize((string) $row['data'], ['allowed_classes' => false]);
            if (false === $value && 'b:0;' !== $row['data']) {
                // Not valid PHP-serialized data: store as JSON null to keep the column castable to JSON.
                $this->addSql(
                    'UPDATE user_log_entries SET data = :data WHERE id = :id',
                    ['data' => 'null', 'id' => $row['id']]
                );
                continue;
            }

            $json = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->addSql(
                'UPDATE user_log_entries SET data = :data WHERE id = :id',
                ['data' => $json, 'id' => $row['id']]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, data FROM user_log_entries WHERE data IS NOT NULL AND data <> ''"
        );

        foreach ($rows as $row) {
            $value = json_decode((string) $row['data'], true);
            $this->addSql(
                'UPDATE user_log_entries SET data = :data WHERE id = :id',
                ['data' => serialize($value), 'id' => $row['id']]
            );
        }
    }
}
