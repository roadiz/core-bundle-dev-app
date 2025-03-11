<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306094643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropped phone, job and birthday columns from users table';
    }

    public function up(Schema $schema): void
    {
        // Fetch users with not null phone, job or birthday and throw exception if any
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE phone IS NOT NULL OR job IS NOT NULL OR birthday IS NOT NULL');
        $result = $stmt->executeQuery();
        $count = $result->fetchOne();

        $this->abortIf(
            is_numeric($count) && $count > 0,
            'Cannot drop phone, job or birthday columns from users table because there are users with not null values.'
        );

        $this->addSql('ALTER TABLE users DROP phone, DROP job, DROP birthday');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD phone VARCHAR(50) DEFAULT NULL, ADD job VARCHAR(250) DEFAULT NULL, ADD birthday DATETIME DEFAULT NULL');
    }
}
