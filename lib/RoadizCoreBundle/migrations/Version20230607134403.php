<?php

declare(strict_types=1);

namespace RZ\Roadiz\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607134403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set all string fields length explicitly.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_translations CHANGE label label VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE custom_form_answers CHANGE ip ip VARCHAR(46) NOT NULL');
        $this->addSql('ALTER TABLE custom_form_fields CHANGE name name VARCHAR(250) NOT NULL, CHANGE label label VARCHAR(250) NOT NULL, CHANGE placeholder placeholder VARCHAR(250) DEFAULT NULL, CHANGE group_name group_name VARCHAR(250) DEFAULT NULL, CHANGE group_name_canonical group_name_canonical VARCHAR(250) DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_forms CHANGE color color VARCHAR(7) DEFAULT NULL, CHANGE name name VARCHAR(250) NOT NULL, CHANGE display_name display_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE documents CHANGE embedId embedId VARCHAR(250) DEFAULT NULL, CHANGE embedPlatform embedPlatform VARCHAR(100) DEFAULT NULL, CHANGE filename filename VARCHAR(250) DEFAULT NULL, CHANGE folder folder VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE documents_translations CHANGE name name VARCHAR(250) DEFAULT NULL');
        $this->addSql('ALTER TABLE folders CHANGE folder_name folder_name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE folders_translations CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE log CHANGE client_ip client_ip VARCHAR(46) DEFAULT NULL, CHANGE channel channel VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE login_attempts CHANGE ip_address ip_address VARCHAR(46) DEFAULT NULL');
        $this->addSql('ALTER TABLE node_type_fields CHANGE name name VARCHAR(250) NOT NULL, CHANGE label label VARCHAR(250) NOT NULL, CHANGE placeholder placeholder VARCHAR(250) DEFAULT NULL, CHANGE group_name group_name VARCHAR(250) DEFAULT NULL, CHANGE group_name_canonical group_name_canonical VARCHAR(250) DEFAULT NULL');
        $this->addSql('ALTER TABLE node_types CHANGE name name VARCHAR(30) NOT NULL, CHANGE display_name display_name VARCHAR(250) NOT NULL, CHANGE color color VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE nodes CHANGE children_order children_order VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE nodes_sources CHANGE title title VARCHAR(250) DEFAULT NULL, CHANGE meta_title meta_title VARCHAR(150) NOT NULL');
        $this->addSql('ALTER TABLE roles CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE settings CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE settings_groups CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE tags CHANGE tag_name tag_name VARCHAR(250) NOT NULL, CHANGE children_order children_order VARCHAR(60) DEFAULT \'position\' NOT NULL');
        $this->addSql('ALTER TABLE tags_translations CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE translations CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE url_aliases CHANGE alias alias VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE usergroups CHANGE name name VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE facebook_name facebook_name VARCHAR(128) DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(128) DEFAULT NULL, CHANGE username username VARCHAR(200) NOT NULL, CHANGE salt salt VARCHAR(64) NOT NULL, CHANGE password password VARCHAR(128) NOT NULL, CHANGE email email VARCHAR(200) NOT NULL, CHANGE firstName firstName VARCHAR(250) DEFAULT NULL, CHANGE lastName lastName VARCHAR(250) DEFAULT NULL, CHANGE phone phone VARCHAR(50) DEFAULT NULL, CHANGE company company VARCHAR(250) DEFAULT NULL, CHANGE job job VARCHAR(250) DEFAULT NULL, CHANGE publicName publicName VARCHAR(250) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_translations CHANGE label label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE custom_form_answers CHANGE ip ip VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE custom_form_fields CHANGE group_name group_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE group_name_canonical group_name_canonical VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE label label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE placeholder placeholder VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE custom_forms CHANGE color color VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE display_name display_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE documents CHANGE embedId embedId VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE embedPlatform embedPlatform VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE filename filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE folder folder VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE documents_translations CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE folders CHANGE folder_name folder_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE folders_translations CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE log CHANGE client_ip client_ip VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE channel channel VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE login_attempts CHANGE ip_address ip_address VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE node_type_fields CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE group_name group_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE group_name_canonical group_name_canonical VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE label label VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE placeholder placeholder VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE node_types CHANGE color color VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE display_name display_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE nodes CHANGE children_order children_order VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE nodes_sources CHANGE title title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE meta_title meta_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE roles CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE settings CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE settings_groups CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tags CHANGE tag_name tag_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE children_order children_order VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'position\' NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tags_translations CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE translations CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE url_aliases CHANGE alias alias VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE usergroups CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE facebook_name facebook_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE confirmation_token confirmation_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE username username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE salt salt VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE publicName publicName VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE firstName firstName VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lastName lastName VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE company company VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE job job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
