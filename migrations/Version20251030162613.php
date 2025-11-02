<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030162613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, post_id INT DEFAULT NULL, replying_to_id INT DEFAULT NULL, content VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', visibility VARCHAR(255) NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526C4B89032C (post_id), INDEX IDX_9474526CCDC1BC7C (replying_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commerce (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, type VARCHAR(50) NOT NULL, coords_x DOUBLE PRECISION NOT NULL, coords_y DOUBLE PRECISION NOT NULL, address VARCHAR(255) NOT NULL, verified TINYINT(1) NOT NULL, contact_info JSON NOT NULL COMMENT \'(DC2Type:json)\', payment_methods JSON NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commerce_image (id INT AUTO_INCREMENT NOT NULL, commerce_id INT NOT NULL, image_path VARCHAR(255) NOT NULL, INDEX IDX_B58FC39AB09114B7 (commerce_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commerce_report (id INT AUTO_INCREMENT NOT NULL, commerce_id INT NOT NULL, user_id INT DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type VARCHAR(255) NOT NULL, content VARCHAR(1000) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, INDEX IDX_2F51F37CB09114B7 (commerce_id), INDEX IDX_2F51F37CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commerce_schedule (id INT AUTO_INCREMENT NOT NULL, commerce_id INT NOT NULL, weekday INT NOT NULL, opens_at TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', closes_at TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_6C88D609B09114B7 (commerce_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', views INT NOT NULL, visibility VARCHAR(255) NOT NULL, INDEX IDX_5A8A6C8DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_tag_post (post_tag_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_B685A9A8AF08774 (post_tag_id), INDEX IDX_B685A9A4B89032C (post_id), PRIMARY KEY(post_tag_id, post_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, commerce_id INT NOT NULL, name VARCHAR(50) NOT NULL, brand VARCHAR(50) NOT NULL, category VARCHAR(50) NOT NULL, verified TINYINT(1) NOT NULL, image_path VARCHAR(255) NOT NULL, INDEX IDX_D34A04ADB09114B7 (commerce_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_report (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, user_id INT DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type VARCHAR(255) NOT NULL, content VARCHAR(1000) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, INDEX IDX_A65336204584665A (product_id), INDEX IDX_A6533620A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_restriction (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, restriction VARCHAR(255) NOT NULL, INDEX IDX_D6B605D14584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, commerce_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', positive TINYINT(1) NOT NULL, content VARCHAR(500) NOT NULL, useful INT NOT NULL, visibility VARCHAR(255) NOT NULL, INDEX IDX_794381C6B09114B7 (commerce_id), INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(40) NOT NULL, email VARCHAR(320) NOT NULL, password VARCHAR(255) NOT NULL, verification VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', role LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', alimentary_restrictions LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', profile_picture VARCHAR(255) DEFAULT NULL, points INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_gamification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, points INT NOT NULL, event VARCHAR(255) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2BFCB17DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CCDC1BC7C FOREIGN KEY (replying_to_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE commerce_image ADD CONSTRAINT FK_B58FC39AB09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE commerce_report ADD CONSTRAINT FK_2F51F37CB09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE commerce_report ADD CONSTRAINT FK_2F51F37CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commerce_schedule ADD CONSTRAINT FK_6C88D609B09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_tag_post ADD CONSTRAINT FK_B685A9A8AF08774 FOREIGN KEY (post_tag_id) REFERENCES post_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_tag_post ADD CONSTRAINT FK_B685A9A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADB09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE product_report ADD CONSTRAINT FK_A65336204584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_report ADD CONSTRAINT FK_A6533620A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_restriction ADD CONSTRAINT FK_D6B605D14584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6B09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_gamification ADD CONSTRAINT FK_2BFCB17DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4B89032C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CCDC1BC7C');
        $this->addSql('ALTER TABLE commerce_image DROP FOREIGN KEY FK_B58FC39AB09114B7');
        $this->addSql('ALTER TABLE commerce_report DROP FOREIGN KEY FK_2F51F37CB09114B7');
        $this->addSql('ALTER TABLE commerce_report DROP FOREIGN KEY FK_2F51F37CA76ED395');
        $this->addSql('ALTER TABLE commerce_schedule DROP FOREIGN KEY FK_6C88D609B09114B7');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post_tag_post DROP FOREIGN KEY FK_B685A9A8AF08774');
        $this->addSql('ALTER TABLE post_tag_post DROP FOREIGN KEY FK_B685A9A4B89032C');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADB09114B7');
        $this->addSql('ALTER TABLE product_report DROP FOREIGN KEY FK_A65336204584665A');
        $this->addSql('ALTER TABLE product_report DROP FOREIGN KEY FK_A6533620A76ED395');
        $this->addSql('ALTER TABLE product_restriction DROP FOREIGN KEY FK_D6B605D14584665A');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6B09114B7');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE user_gamification DROP FOREIGN KEY FK_2BFCB17DA76ED395');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE commerce');
        $this->addSql('DROP TABLE commerce_image');
        $this->addSql('DROP TABLE commerce_report');
        $this->addSql('DROP TABLE commerce_schedule');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_tag');
        $this->addSql('DROP TABLE post_tag_post');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_report');
        $this->addSql('DROP TABLE product_restriction');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_gamification');
    }
}
