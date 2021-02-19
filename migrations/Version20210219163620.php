<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210219163620 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE transaction SET method_id = 1 WHERE method_id IS NULL;');
        $this->addSql('ALTER TABLE transaction CHANGE store_id store_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE type_id type_id INT NOT NULL, CHANGE method_id method_id INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction CHANGE store_id store_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE type_id type_id INT DEFAULT NULL, CHANGE method_id method_id INT DEFAULT NULL');
    }

    /**
     * @todo remove
     */
    public function isTransactional(): bool
    {
        return false;
    }
}
