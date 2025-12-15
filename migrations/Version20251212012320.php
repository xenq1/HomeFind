<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212012320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F6479D86650F');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F6479D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE property ADD rented_by_id INT DEFAULT NULL, ADD bought_by_id INT DEFAULT NULL, ADD rented_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD bought_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE86A9EE76 FOREIGN KEY (rented_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEDEC6D6BA FOREIGN KEY (bought_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8BF21CDE86A9EE76 ON property (rented_by_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDEDEC6D6BA ON property (bought_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_log DROP FOREIGN KEY FK_FD06F6479D86650F');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F6479D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE86A9EE76');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDEDEC6D6BA');
        $this->addSql('DROP INDEX IDX_8BF21CDE86A9EE76 ON property');
        $this->addSql('DROP INDEX IDX_8BF21CDEDEC6D6BA ON property');
        $this->addSql('ALTER TABLE property DROP rented_by_id, DROP bought_by_id, DROP rented_at, DROP bought_at');
    }
}
