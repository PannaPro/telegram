<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123183626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP CONSTRAINT fk_3bae0aa755102661');
        $this->addSql('DROP INDEX uniq_3bae0aa755102661');
        $this->addSql('ALTER TABLE event RENAME COLUMN game_group_id TO event_group_id');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B8B83097 FOREIGN KEY (event_group_id) REFERENCES telegram_event_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7B8B83097 ON event (event_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7B8B83097');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7B8B83097');
        $this->addSql('ALTER TABLE event RENAME COLUMN event_group_id TO game_group_id');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT fk_3bae0aa755102661 FOREIGN KEY (game_group_id) REFERENCES telegram_event_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_3bae0aa755102661 ON event (game_group_id)');
    }
}
