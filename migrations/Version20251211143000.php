<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251211143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at column to user and backfill existing rows';
    }

    public function up(Schema $schema): void
    {
        // Add nullable column first
        $this->addSql('ALTER TABLE `user` ADD created_at DATETIME DEFAULT NULL');

        // Backfill existing users with current timestamp
        $this->addSql('UPDATE `user` SET created_at = NOW() WHERE created_at IS NULL');

        // Make column NOT NULL
        $this->addSql('ALTER TABLE `user` MODIFY created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP created_at');
    }
}
