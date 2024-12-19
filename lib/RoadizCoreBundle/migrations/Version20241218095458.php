<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218095458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added normalization_context to NodeTypeField';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_type_fields ADD normalization_context JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE node_type_fields DROP normalization_context');
    }
}
