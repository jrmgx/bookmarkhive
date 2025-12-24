<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Helper\UrlHelper;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224092933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Add column as nullable first
        $this->connection->executeStatement('ALTER TABLE bookmark ADD normalized_url TEXT');

        // Update existing rows with normalized URLs
        $bookmarks = $this->connection->fetchAllAssociative('SELECT id, url FROM bookmark');
        foreach ($bookmarks as $bookmark) {
            $normalizedUrl = UrlHelper::normalize($bookmark['url']);
            $this->connection->executeStatement(
                'UPDATE bookmark SET normalized_url = :normalized_url WHERE id = :id',
                [
                    'normalized_url' => $normalizedUrl,
                    'id' => $bookmark['id'],
                ],
                [
                    'normalized_url' => \Doctrine\DBAL\ParameterType::STRING,
                    'id' => \Doctrine\DBAL\ParameterType::STRING,
                ]
            );
        }

        // Now make it NOT NULL
        $this->connection->executeStatement('ALTER TABLE bookmark ALTER COLUMN normalized_url SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bookmark DROP normalized_url');
    }
}
