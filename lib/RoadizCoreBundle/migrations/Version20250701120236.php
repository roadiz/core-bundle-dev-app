<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701120236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Roles 3/3] Remove roles entities and relations.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE realms DROP FOREIGN KEY FK_7DF2621AD60322AC');
        $this->addSql('ALTER TABLE groups_roles DROP FOREIGN KEY FK_E79D4963D60322AC');
        $this->addSql('ALTER TABLE groups_roles DROP FOREIGN KEY FK_E79D4963FE54D947');
        $this->addSql('ALTER TABLE users_roles DROP FOREIGN KEY FK_51498A8ED60322AC');
        $this->addSql('ALTER TABLE users_roles DROP FOREIGN KEY FK_51498A8EA76ED395');
        $this->addSql('DROP TABLE groups_roles');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE users_roles');
        $this->addSql('DROP INDEX IDX_7DF2621AD60322AC ON realms');
        $this->addSql('ALTER TABLE realms DROP role_id');
        $this->addSql('DROP INDEX IDX_1483A5E98B8E8428 ON users');
        $this->addSql('DROP INDEX IDX_1483A5E943625D9F ON users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groups_roles (group_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_E79D4963FE54D947 (group_id), INDEX IDX_E79D4963D60322AC (role_id), PRIMARY KEY(group_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(250) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users_roles (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_51498A8EA76ED395 (user_id), INDEX IDX_51498A8ED60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE realms ADD role_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE groups_roles ADD CONSTRAINT FK_E79D4963D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE groups_roles ADD CONSTRAINT FK_E79D4963FE54D947 FOREIGN KEY (group_id) REFERENCES usergroups (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8ED60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_roles ADD CONSTRAINT FK_51498A8EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE realms ADD CONSTRAINT FK_7DF2621AD60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7DF2621AD60322AC ON realms (role_id)');
        $this->addSql('CREATE INDEX IDX_1483A5E98B8E8428 ON users (created_at)');
        $this->addSql('CREATE INDEX IDX_1483A5E943625D9F ON users (updated_at)');

        // Get all roles from users and usergroups tables and create roles entities
        $userRolesNames = $this->connection->fetchAllAssociative(<<<SQL
SELECT user_roles AS roles, id AS user_id FROM users
WHERE user_roles IS NOT NULL
SQL);
        $groupRolesNames = $this->connection->fetchAllAssociative(<<<SQL
SELECT group_roles as roles, id AS group_id FROM usergroups
WHERE group_roles IS NOT NULL
SQL);
        $realmsRolesNames = $this->connection->fetchAllAssociative(<<<SQL
SELECT role, id AS realm_id FROM realms
WHERE role IS NOT NULL
SQL);

        $allRoles = [];
        foreach ($userRolesNames as $roleName) {
            $allRoles = [...$allRoles, ...json_decode($roleName['roles'], true)];
        }
        foreach ($groupRolesNames as $roleName) {
            $allRoles = [...$allRoles, ...json_decode($roleName['roles'], true)];
        }
        foreach ($realmsRolesNames as $roleName) {
            $allRoles = [...$allRoles, $roleName['role']];
        }

        /*
         * Recreate roles entities with known IDs.
         */
        $allRoles = array_unique(array_filter($allRoles));
        foreach ($allRoles as $roleId => $roleName) {
            // Create role entity if not exists
            $this->addSql(<<<SQL
INSERT INTO roles (id, name) VALUES (:roleId, :roleName)
SQL, ['roleId' => ($roleId + 1), 'roleName' => $roleName]);
        }
    }
}
