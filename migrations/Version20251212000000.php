<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add cascade delete to activity_log foreign key
 */
final class Version20251212000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cascade delete to activity_log user_id foreign key';
    }

    public function up(Schema $schema): void
    {
        // Drop the existing foreign key
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F6479D86650F');
        
        // Add the new foreign key with ON DELETE CASCADE
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F6479D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop the foreign key
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F6479D86650F');
        
        // Restore the original foreign key without cascade
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F6479D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
    }
}
