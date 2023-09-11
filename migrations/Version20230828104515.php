<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration automatiquement générée : Veuillez la modifier selon vos besoins !
 */
final class Version20230828104515 extends AbstractMigration
{
    public function getDescription(): string
    {
        // Retourne une description de la migration (vide dans cet exemple)
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cette migration up() est générée automatiquement, veuillez la modifier selon vos besoins
        // Elle crée une table "user" avec des colonnes spécifiées
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // Cette migration down() est générée automatiquement, veuillez la modifier selon vos besoins
        // Elle supprime la table "user"
        $this->addSql('DROP TABLE user');
    }
}
