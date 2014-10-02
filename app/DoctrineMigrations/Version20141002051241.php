<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141002051241 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE job CHANGE position position VARCHAR(100) NOT NULL, CHANGE location location VARCHAR(100) NOT NULL, CHANGE is_activated is_activated TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(40) NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE job CHANGE position position VARCHAR(255) NOT NULL, CHANGE location location VARCHAR(255) NOT NULL, CHANGE is_activated is_activated TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(255) NOT NULL');
    }
}
