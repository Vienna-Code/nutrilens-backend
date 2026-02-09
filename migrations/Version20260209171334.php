<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209171334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD tagging_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CFDA1664 FOREIGN KEY (tagging_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9474526CFDA1664 ON comment (tagging_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CFDA1664');
        $this->addSql('DROP INDEX IDX_9474526CFDA1664 ON comment');
        $this->addSql('ALTER TABLE comment DROP tagging_user_id');
    }
}
