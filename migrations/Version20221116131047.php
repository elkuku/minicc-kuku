<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221116131047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('DROP TABLE user_state');
        $this->addSql('DROP INDEX IDX_8D93D6495D83CC1 ON user');
        $this->addSql('ALTER TABLE user DROP state_id, DROP status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_state (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user ADD state_id INT DEFAULT NULL, ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES user_state (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495D83CC1 ON user (state_id)');
    }
}
