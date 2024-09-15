<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915174436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', cca3 VARCHAR(3) NOT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(255) NOT NULL, subregion VARCHAR(255) DEFAULT NULL, demonym LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', population INT NOT NULL, independant TINYINT(1) NOT NULL, flag VARCHAR(255) NOT NULL, currency LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_5373C9668170C978 (cca3), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE country');
    }
}
