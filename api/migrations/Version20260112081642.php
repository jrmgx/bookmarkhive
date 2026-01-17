<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112081642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ADD inbox_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD outbox_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD shared_inbox_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD follower_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD following_url TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account DROP inbox_url');
        $this->addSql('ALTER TABLE account DROP outbox_url');
        $this->addSql('ALTER TABLE account DROP shared_inbox_url');
        $this->addSql('ALTER TABLE account DROP follower_url');
        $this->addSql('ALTER TABLE account DROP following_url');
    }
}
