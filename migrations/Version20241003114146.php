<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003114146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event (id INT NOT NULL, date DATE NOT NULL, event_name VARCHAR(100) NOT NULL, tournament_name VARCHAR(100) NOT NULL, entrants INT NOT NULL, notables INT NOT NULL, tier VARCHAR(255) NOT NULL, region VARCHAR(10) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE set ADD event_id INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE set DROP event_name');
        $this->addSql('ALTER TABLE set DROP tournament_name');
        $this->addSql('ALTER TABLE set ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN set.date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE event');
        $this->addSql('ALTER TABLE set ADD event_name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE set ADD tournament_name VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE set DROP event_id');
        $this->addSql('ALTER TABLE set ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN set.date IS NULL');
    }
}
