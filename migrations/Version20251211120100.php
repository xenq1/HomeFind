<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251211120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop bedrooms and bathrooms columns from property table';
    }

    public function up(Schema $schema): void
    {
        // Drop the two columns that the entity no longer uses
        $this->addSql('ALTER TABLE property DROP COLUMN bedrooms, DROP COLUMN bathrooms');
    }

    public function down(Schema $schema): void
    {
        // Recreate the columns with a default value to be safe
        $this->addSql('ALTER TABLE property ADD bedrooms INT NOT NULL DEFAULT 0, ADD bathrooms INT NOT NULL DEFAULT 0');
    }
}
