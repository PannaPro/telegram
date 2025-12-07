<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251207163205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE telegram_user ADD referral_link VARCHAR(255) NOT NULL DEFAULT 'unknown'");
        $this->addSql('ALTER TABLE telegram_user ADD referred_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE telegram_user ADD CONSTRAINT FK_F180F059758C8114 FOREIGN KEY (referred_by_id) REFERENCES telegram_user (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_F180F059758C8114 ON telegram_user (referred_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE telegram_user DROP CONSTRAINT FK_F180F059758C8114');
        $this->addSql('DROP INDEX IDX_F180F059758C8114');
        $this->addSql('ALTER TABLE telegram_user DROP referral_link');
        $this->addSql('ALTER TABLE telegram_user DROP referred_by_id');
    }
}
