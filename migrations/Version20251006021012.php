<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006021012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE comercio CHANGE verificado verificado TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE comercio_reporte CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE gamificacion CHANGE evento evento VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE producto CHANGE verificado verificado TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE producto_reporte CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE publicacion CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE visitas visitas INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE resena CHANGE fecha fecha DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE util util INT DEFAULT 0 NOT NULL, CHANGE no_util no_util INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE fecha_registro fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE rol rol VARCHAR(255) DEFAULT \'no_verificado\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE comercio CHANGE verificado verificado TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE comercio_reporte CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE gamificacion CHANGE evento evento VARCHAR(255) NOT NULL, CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE producto CHANGE verificado verificado TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE producto_reporte CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE publicacion CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE visitas visitas INT NOT NULL');
        $this->addSql('ALTER TABLE resena CHANGE fecha fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE util util INT NOT NULL, CHANGE no_util no_util INT NOT NULL');
        $this->addSql('ALTER TABLE usuario CHANGE fecha_registro fecha_registro DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE rol rol VARCHAR(255) NOT NULL');
    }
}
