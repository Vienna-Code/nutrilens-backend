<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209130741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCDC1BC7C');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCDC1BC7C FOREIGN KEY (replying_to_id) REFERENCES comment (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCDC1BC7C');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCDC1BC7C FOREIGN KEY (replying_to_id) REFERENCES comment (id)');
    }
}
