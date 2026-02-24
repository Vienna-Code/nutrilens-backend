<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224130646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commerce_image DROP FOREIGN KEY FK_B58FC39AB09114B7');
        $this->addSql('DROP TABLE commerce_image');
        $this->addSql('ALTER TABLE commerce ADD commerce_images JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commerce_image (id INT AUTO_INCREMENT NOT NULL, commerce_id INT NOT NULL, image_path VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, INDEX IDX_B58FC39AB09114B7 (commerce_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commerce_image ADD CONSTRAINT FK_B58FC39AB09114B7 FOREIGN KEY (commerce_id) REFERENCES commerce (id)');
        $this->addSql('ALTER TABLE commerce DROP commerce_images');
    }
}
