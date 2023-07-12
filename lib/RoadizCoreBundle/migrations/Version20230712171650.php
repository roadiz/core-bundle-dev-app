<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230712171650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removed useless login_attempts table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE login_attempts');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE login_attempts (id INT AUTO_INCREMENT NOT NULL, ip_address VARCHAR(46) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', blocks_login_until DATETIME DEFAULT NULL, username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, attempt_count INT DEFAULT NULL, INDEX IDX_9163C7FBEFF8A4EEF85E0677 (blocks_login_until, username), INDEX IDX_9163C7FBEFF8A4EEF85E067722FFD58C (blocks_login_until, username, ip_address), INDEX IDX_9163C7FBF85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
