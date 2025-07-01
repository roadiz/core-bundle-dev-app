<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701113948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[Roles 2/3] Convert all roles to string format in users, usergroups and realm table.';
    }

    public function up(Schema $schema): void
    {
        // Migrate all user_roles to string format in users table
        $usersRoles = $this->connection->fetchAllAssociative(<<<SQL
SELECT ur.user_id AS user_id, r.name AS role FROM users_roles AS ur
INNER JOIN roles AS r ON r.id = ur.role_id
SQL);
        $mergedRoles = [];
        foreach ($usersRoles as $userRole) {
            $userId = $userRole['user_id'];
            $roleName = $userRole['role'];
            if (!isset($mergedRoles[$userId])) {
                $mergedRoles[$userId] = [];
            }
            if (!in_array($roleName, $mergedRoles[$userId], true)) {
                $mergedRoles[$userId][] = $roleName;
            }
        }
        foreach ($mergedRoles as $userId => $roles) {
            $this->addSql(<<<SQL
UPDATE users SET user_roles = :roles
WHERE id = :userId
SQL, [
                'roles' => json_encode(array_values($roles)),
                'userId' => $userId,
            ]);
        }

        // Migrate all groups_roles to string format in users table
        $groupsRoles = $this->connection->fetchAllAssociative(<<<SQL
SELECT gr.group_id AS group_id, r.name AS role FROM groups_roles AS gr
INNER JOIN roles AS r ON r.id = gr.role_id
SQL);
        $mergedRoles = [];
        foreach ($groupsRoles as $groupRole) {
            $groupId = $groupRole['group_id'];
            $roleName = $groupRole['role'];
            if (!isset($mergedRoles[$groupId])) {
                $mergedRoles[$groupId] = [];
            }
            if (!in_array($roleName, $mergedRoles[$groupId], true)) {
                $mergedRoles[$groupId][] = $roleName;
            }
        }
        foreach ($mergedRoles as $groupId => $roles) {
            $this->addSql(<<<SQL
UPDATE usergroups SET group_roles = :roles
WHERE id = :groupId
SQL, [
                'roles' => json_encode(array_values($roles)),
                'groupId' => $groupId,
            ]);
        }

        // Migrate all realm roles to string format in realm table
        $this->addSql(<<<SQL
UPDATE realms, roles SET realms.role = roles.name
WHERE realms.role_id = roles.id
SQL);
    }

    public function down(Schema $schema): void
    {
        // Insert new roles ID in users_roles and groups_roles tables
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

        $allRoles = $this->connection->fetchAllAssociative(<<<SQL
SELECT id, name FROM roles
SQL);
        $allRoles = array_combine(
            array_column($allRoles, 'id'),
            array_column($allRoles, 'name')
        );

        /*
         * Recreate users_roles rows with role IDs.
         */
        foreach ($userRolesNames as $userRole) {
            $roles = json_decode($userRole['roles'], true);
            foreach ($roles as $roleName) {
                $roleId = array_search($roleName, $allRoles, true);
                $this->addSql(<<<SQL
INSERT INTO users_roles (user_id, role_id) VALUES (:userId, :roleId)
SQL, [
                    'userId' => $userRole['user_id'],
                    'roleId' => $roleId,
                ]);
            }
        }

        /*
         * Recreate groups_roles rows with role IDs.
         */
        foreach ($groupRolesNames as $groupRole) {
            $roles = json_decode($groupRole['roles'], true);
            foreach ($roles as $roleName) {
                $roleId = array_search($roleName, $allRoles, true);
                $this->addSql(<<<SQL
INSERT INTO groups_roles (group_id, role_id) VALUES (:groupId, :roleId)
SQL, [
                    'groupId' => $groupRole['group_id'],
                    'roleId' => $roleId,
                ]);
            }
        }

        /*
         * Recreate realm rows with role IDs.
         */
        foreach ($realmsRolesNames as $realmRole) {
            $roleId = array_search($realmRole['role'], $allRoles, true);
            $this->addSql(<<<SQL
UPDATE realms SET role_id = :roleId
WHERE id = :realmId
SQL, [
                'realmId' => $realmRole['realm_id'],
                'roleId' => $roleId,
            ]);
        }
    }
}
