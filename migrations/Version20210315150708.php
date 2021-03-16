<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210315150708 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD deposit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19815E4B1 FOREIGN KEY (deposit_id) REFERENCES deposit (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_723705D19815E4B1 ON transaction (deposit_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19815E4B1');
        $this->addSql('DROP INDEX UNIQ_723705D19815E4B1 ON transaction');
        $this->addSql('ALTER TABLE transaction DROP deposit_id');
    }

    /**
     * @todo remove
     */
    public function isTransactional(): bool
    {
        return false;
    }

}
