<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250103191154 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE placement DROP CONSTRAINT fk_48db750e71f7e88b');
        $this->addSql('DROP INDEX idx_48db750e71f7e88b');
        $this->addSql('ALTER TABLE player ALTER tag TYPE VARCHAR(50)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player ALTER tag TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE placement ADD CONSTRAINT fk_48db750e71f7e88b FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_48db750e71f7e88b ON placement (event_id)');
    }
}
