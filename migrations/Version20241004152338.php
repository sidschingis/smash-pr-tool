<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004152338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE placement (player_id INT NOT NULL, event_id INT NOT NULL, placement INT NOT NULL, score INT NOT NULL, PRIMARY KEY(player_id, event_id))');
        $this->addSql('ALTER TABLE set ALTER display_score TYPE VARCHAR(100)');
        $this->addSql('CREATE INDEX by_event ON set (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE placement');
        $this->addSql('DROP INDEX by_event');
        $this->addSql('ALTER TABLE set ALTER display_score TYPE VARCHAR(50)');
    }
}
