<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221116162302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP FOREIGN KEY FK_E98F2859708A0E0');
        $this->addSql('DROP INDEX IDX_E98F2859708A0E0 ON contract');
        $this->addSql('ALTER TABLE contract ADD gender VARCHAR(255) NOT NULL, DROP gender_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract ADD gender_id INT DEFAULT NULL, DROP gender');
        $this->addSql('ALTER TABLE contract ADD CONSTRAINT FK_E98F2859708A0E0 FOREIGN KEY (gender_id) REFERENCES user_gender (id)');
        $this->addSql('CREATE INDEX IDX_E98F2859708A0E0 ON contract (gender_id)');
    }
}
