<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240819142110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX by_loser');
        $this->addSql('DROP INDEX by_winner');
        $this->addSql('CREATE INDEX by_loser ON set (loser_id, date)');
        $this->addSql('CREATE INDEX by_winner ON set (winner_id, date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX by_winner');
        $this->addSql('DROP INDEX by_loser');
        $this->addSql('CREATE INDEX by_winner ON set (winner_id)');
        $this->addSql('CREATE INDEX by_loser ON set (loser_id)');
    }
}
