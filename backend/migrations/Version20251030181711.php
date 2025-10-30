<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030181711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE big_folk_district (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blackout (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, initiator_name VARCHAR(255) DEFAULT NULL, source VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blackout_building (blackout_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', building_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_11A1210F81E4A992 (blackout_id), INDEX IDX_11A1210F4D2A7E12 (building_id), PRIMARY KEY(blackout_id, building_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', street_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', district_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', folk_district_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', big_folk_district_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', city_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', number VARCHAR(50) DEFAULT NULL, is_fake TINYINT(1) NOT NULL, type VARCHAR(100) DEFAULT NULL, coordinates JSON DEFAULT NULL, INDEX IDX_E16F61D487CF8EB (street_id), INDEX IDX_E16F61D4B08FA272 (district_id), INDEX IDX_E16F61D44A288452 (folk_district_id), INDEX IDX_E16F61D47333D892 (big_folk_district_id), INDEX IDX_E16F61D48BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE city (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE district (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folk_district (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE street (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', city_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, INDEX IDX_F0EED3D88BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blackout_building ADD CONSTRAINT FK_11A1210F81E4A992 FOREIGN KEY (blackout_id) REFERENCES blackout (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blackout_building ADD CONSTRAINT FK_11A1210F4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D487CF8EB FOREIGN KEY (street_id) REFERENCES street (id)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D4B08FA272 FOREIGN KEY (district_id) REFERENCES district (id)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D44A288452 FOREIGN KEY (folk_district_id) REFERENCES folk_district (id)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D47333D892 FOREIGN KEY (big_folk_district_id) REFERENCES big_folk_district (id)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D48BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE street ADD CONSTRAINT FK_F0EED3D88BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blackout_building DROP FOREIGN KEY FK_11A1210F81E4A992');
        $this->addSql('ALTER TABLE blackout_building DROP FOREIGN KEY FK_11A1210F4D2A7E12');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D487CF8EB');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D4B08FA272');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D44A288452');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D47333D892');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D48BAC62AF');
        $this->addSql('ALTER TABLE street DROP FOREIGN KEY FK_F0EED3D88BAC62AF');
        $this->addSql('DROP TABLE big_folk_district');
        $this->addSql('DROP TABLE blackout');
        $this->addSql('DROP TABLE blackout_building');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE district');
        $this->addSql('DROP TABLE folk_district');
        $this->addSql('DROP TABLE street');
    }
}
