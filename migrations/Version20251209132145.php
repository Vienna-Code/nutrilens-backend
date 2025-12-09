<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209132145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_tag (post_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_5ACE3AF04B89032C (post_id), INDEX IDX_5ACE3AF0BAD26311 (tag_id), PRIMARY KEY(post_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF04B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_tag ADD CONSTRAINT FK_5ACE3AF0BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33BBAD26311');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B4B89032C');
        $this->addSql('DROP TABLE tag_post');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCDC1BC7C');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCDC1BC7C FOREIGN KEY (replying_to_id) REFERENCES comment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_post (tag_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_B485D33B4B89032C (post_id), INDEX IDX_B485D33BBAD26311 (tag_id), PRIMARY KEY(tag_id, post_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_tag DROP FOREIGN KEY FK_5ACE3AF04B89032C');
        $this->addSql('ALTER TABLE post_tag DROP FOREIGN KEY FK_5ACE3AF0BAD26311');
        $this->addSql('DROP TABLE post_tag');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCDC1BC7C');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCDC1BC7C FOREIGN KEY (replying_to_id) REFERENCES comment (id) ON DELETE SET NULL');
    }
}
