<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003104225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player ADD region VARCHAR(50) DEFAULT \'\' NOT NULL');
        $this->addSql('CREATE INDEX search_tag ON player (tag)');
        $this->addSql('CREATE INDEX search_region ON player (region)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX search_tag');
        $this->addSql('DROP INDEX search_region');
        $this->addSql('ALTER TABLE player DROP region');
    }
}
