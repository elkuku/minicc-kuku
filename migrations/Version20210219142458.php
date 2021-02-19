<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210219142458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, gender_id INT DEFAULT NULL, store_number INT NOT NULL, inq_nombreapellido VARCHAR(150) NOT NULL, inq_ci VARCHAR(11) NOT NULL, destination VARCHAR(50) NOT NULL, val_alq DOUBLE PRECISION NOT NULL, val_garantia DOUBLE PRECISION NOT NULL, date DATE NOT NULL, cnt_lanfort INT NOT NULL, cnt_neon INT NOT NULL, cnt_switch INT NOT NULL, cnt_toma INT NOT NULL, cnt_ventana INT NOT NULL, cnt_llaves INT NOT NULL, cnt_med_agua INT NOT NULL, cnt_med_elec INT NOT NULL, med_electrico VARCHAR(50) NOT NULL, med_agua VARCHAR(50) NOT NULL, text TEXT NOT NULL, INDEX IDX_E98F2859708A0E0 (gender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE deposit (id INT AUTO_INCREMENT NOT NULL, entity_id INT DEFAULT NULL, date DATE NOT NULL, document VARCHAR(150) NOT NULL, amount NUMERIC(13, 2) NOT NULL, INDEX IDX_95DB9D3981257D5D (entity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE store (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, destination VARCHAR(50) NOT NULL, val_alq DOUBLE PRECISION NOT NULL, cnt_lanfort INT NOT NULL, cnt_neon INT NOT NULL, cnt_switch INT NOT NULL, cnt_toma INT NOT NULL, cnt_ventana INT NOT NULL, cnt_llaves INT NOT NULL, cnt_med_agua INT NOT NULL, cnt_med_elec INT NOT NULL, med_electrico VARCHAR(50) NOT NULL, med_agua VARCHAR(50) NOT NULL, INDEX IDX_FF575877A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, store_id INT DEFAULT NULL, user_id INT DEFAULT NULL, type_id INT DEFAULT NULL, method_id INT DEFAULT NULL, date DATE NOT NULL, amount NUMERIC(13, 2) NOT NULL, document INT DEFAULT NULL, dep_id INT DEFAULT NULL, recipe_no INT DEFAULT NULL, INDEX IDX_723705D1B092A811 (store_id), INDEX IDX_723705D1A76ED395 (user_id), INDEX IDX_723705D1C54C8C93 (type_id), INDEX IDX_723705D119883967 (method_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, gender_id INT DEFAULT NULL, state_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(40) NOT NULL, role VARCHAR(50) NOT NULL, inq_ci VARCHAR(50) NOT NULL, inq_ruc VARCHAR(13) DEFAULT NULL, telefono VARCHAR(25) DEFAULT NULL, telefono2 VARCHAR(25) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649708A0E0 (gender_id), INDEX IDX_8D93D6495D83CC1 (state_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_gender (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_state (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859708A0E0 FOREIGN KEY (gender_id) REFERENCES user_gender (id)');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D3981257D5D FOREIGN KEY (entity_id) REFERENCES payment_method (id)');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF575877A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1B092A811 FOREIGN KEY (store_id) REFERENCES store (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1C54C8C93 FOREIGN KEY (type_id) REFERENCES transaction_type (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D119883967 FOREIGN KEY (method_id) REFERENCES payment_method (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649708A0E0 FOREIGN KEY (gender_id) REFERENCES user_gender (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495D83CC1 FOREIGN KEY (state_id) REFERENCES user_state (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D3981257D5D');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D119883967');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1B092A811');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1C54C8C93');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF575877A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859708A0E0');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649708A0E0');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495D83CC1');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE store');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE transaction_type');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_gender');
        $this->addSql('DROP TABLE user_state');
    }

    /**
     * @todo remove
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
