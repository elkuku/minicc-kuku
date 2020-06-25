<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200625203141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE inq_ruc inq_ruc VARCHAR(13) DEFAULT NULL, CHANGE telefono telefono VARCHAR(25) DEFAULT NULL, CHANGE telefono2 telefono2 VARCHAR(25) DEFAULT NULL, CHANGE direccion direccion VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE inq_ruc inq_ruc VARCHAR(13) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE telefono telefono VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE telefono2 telefono2 VARCHAR(25) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE direccion direccion VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
