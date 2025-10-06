<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006004134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comentario (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, publicacion_id INT NOT NULL, contenido VARCHAR(500) NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4B91E702DB38439E (usuario_id), INDEX IDX_4B91E7029ACBB5E7 (publicacion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comercio (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(80) NOT NULL, tipo VARCHAR(50) NOT NULL, coordenadas VARCHAR(255) NOT NULL, horarios VARCHAR(255) NOT NULL, foto_fachada VARCHAR(255) DEFAULT NULL, metodos_pago VARCHAR(255) DEFAULT NULL, contacto VARCHAR(255) DEFAULT NULL, verificado TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comercio_imagen (id INT AUTO_INCREMENT NOT NULL, comercio_id INT NOT NULL, ruta VARCHAR(255) NOT NULL, INDEX IDX_25248D7D2C8A84B9 (comercio_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comercio_reporte (id INT AUTO_INCREMENT NOT NULL, comercio_id INT NOT NULL, usuario_id INT NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', contenido VARCHAR(1000) NOT NULL, INDEX IDX_79B1C0FC2C8A84B9 (comercio_id), INDEX IDX_79B1C0FCDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gamificacion (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, puntos INT NOT NULL, evento VARCHAR(255) NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_66FEA259DB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, comercio_id INT NOT NULL, nombre VARCHAR(80) NOT NULL, marca VARCHAR(80) NOT NULL, categoria VARCHAR(80) NOT NULL, verificado TINYINT(1) NOT NULL, condicion LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', INDEX IDX_A7BB06152C8A84B9 (comercio_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto_imagen (id INT AUTO_INCREMENT NOT NULL, producto_id INT NOT NULL, ruta VARCHAR(255) NOT NULL, INDEX IDX_2E3E7DFD7645698E (producto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE producto_reporte (id INT AUTO_INCREMENT NOT NULL, producto_id INT NOT NULL, usuario_id INT NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', contenido VARCHAR(1000) NOT NULL, INDEX IDX_9402592C7645698E (producto_id), INDEX IDX_9402592CDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publicacion (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, titulo VARCHAR(200) NOT NULL, contenido LONGTEXT NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', visitas INT NOT NULL, INDEX IDX_62F2085FDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publicacion_etiqueta (id INT AUTO_INCREMENT NOT NULL, publicacion_id INT NOT NULL, INDEX IDX_8B4FF3E99ACBB5E7 (publicacion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resena (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, comercio_id INT NOT NULL, fecha DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', calificacion TINYINT(1) NOT NULL, contenido VARCHAR(1000) NOT NULL, util INT NOT NULL, no_util INT NOT NULL, INDEX IDX_50A7E40ADB38439E (usuario_id), INDEX IDX_50A7E40A2C8A84B9 (comercio_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(40) NOT NULL, email VARCHAR(320) NOT NULL, verificacion_email VARCHAR(64) DEFAULT NULL, contrasena VARCHAR(255) NOT NULL, condicion LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', fecha_registro DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rol VARCHAR(255) NOT NULL, foto_perfil VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E702DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E7029ACBB5E7 FOREIGN KEY (publicacion_id) REFERENCES publicacion (id)');
        $this->addSql('ALTER TABLE comercio_imagen ADD CONSTRAINT FK_25248D7D2C8A84B9 FOREIGN KEY (comercio_id) REFERENCES comercio (id)');
        $this->addSql('ALTER TABLE comercio_reporte ADD CONSTRAINT FK_79B1C0FC2C8A84B9 FOREIGN KEY (comercio_id) REFERENCES comercio (id)');
        $this->addSql('ALTER TABLE comercio_reporte ADD CONSTRAINT FK_79B1C0FCDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE gamificacion ADD CONSTRAINT FK_66FEA259DB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE producto ADD CONSTRAINT FK_A7BB06152C8A84B9 FOREIGN KEY (comercio_id) REFERENCES comercio (id)');
        $this->addSql('ALTER TABLE producto_imagen ADD CONSTRAINT FK_2E3E7DFD7645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE producto_reporte ADD CONSTRAINT FK_9402592C7645698E FOREIGN KEY (producto_id) REFERENCES producto (id)');
        $this->addSql('ALTER TABLE producto_reporte ADD CONSTRAINT FK_9402592CDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE publicacion ADD CONSTRAINT FK_62F2085FDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE publicacion_etiqueta ADD CONSTRAINT FK_8B4FF3E99ACBB5E7 FOREIGN KEY (publicacion_id) REFERENCES publicacion (id)');
        $this->addSql('ALTER TABLE resena ADD CONSTRAINT FK_50A7E40ADB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE resena ADD CONSTRAINT FK_50A7E40A2C8A84B9 FOREIGN KEY (comercio_id) REFERENCES comercio (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E702DB38439E');
        $this->addSql('ALTER TABLE comentario DROP FOREIGN KEY FK_4B91E7029ACBB5E7');
        $this->addSql('ALTER TABLE comercio_imagen DROP FOREIGN KEY FK_25248D7D2C8A84B9');
        $this->addSql('ALTER TABLE comercio_reporte DROP FOREIGN KEY FK_79B1C0FC2C8A84B9');
        $this->addSql('ALTER TABLE comercio_reporte DROP FOREIGN KEY FK_79B1C0FCDB38439E');
        $this->addSql('ALTER TABLE gamificacion DROP FOREIGN KEY FK_66FEA259DB38439E');
        $this->addSql('ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06152C8A84B9');
        $this->addSql('ALTER TABLE producto_imagen DROP FOREIGN KEY FK_2E3E7DFD7645698E');
        $this->addSql('ALTER TABLE producto_reporte DROP FOREIGN KEY FK_9402592C7645698E');
        $this->addSql('ALTER TABLE producto_reporte DROP FOREIGN KEY FK_9402592CDB38439E');
        $this->addSql('ALTER TABLE publicacion DROP FOREIGN KEY FK_62F2085FDB38439E');
        $this->addSql('ALTER TABLE publicacion_etiqueta DROP FOREIGN KEY FK_8B4FF3E99ACBB5E7');
        $this->addSql('ALTER TABLE resena DROP FOREIGN KEY FK_50A7E40ADB38439E');
        $this->addSql('ALTER TABLE resena DROP FOREIGN KEY FK_50A7E40A2C8A84B9');
        $this->addSql('DROP TABLE comentario');
        $this->addSql('DROP TABLE comercio');
        $this->addSql('DROP TABLE comercio_imagen');
        $this->addSql('DROP TABLE comercio_reporte');
        $this->addSql('DROP TABLE gamificacion');
        $this->addSql('DROP TABLE producto');
        $this->addSql('DROP TABLE producto_imagen');
        $this->addSql('DROP TABLE producto_reporte');
        $this->addSql('DROP TABLE publicacion');
        $this->addSql('DROP TABLE publicacion_etiqueta');
        $this->addSql('DROP TABLE resena');
        $this->addSql('DROP TABLE usuario');
    }
}
