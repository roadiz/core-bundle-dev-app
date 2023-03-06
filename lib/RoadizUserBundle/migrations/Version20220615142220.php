<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615142220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added user_validation_tokens on delete cascade constraint';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_validation_tokens DROP FOREIGN KEY FK_D613F87EA76ED395');
        $this->addSql('ALTER TABLE user_validation_tokens ADD CONSTRAINT FK_D613F87EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_validation_tokens DROP FOREIGN KEY FK_D613F87EA76ED395');
        $this->addSql('ALTER TABLE user_validation_tokens ADD CONSTRAINT FK_D613F87EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
