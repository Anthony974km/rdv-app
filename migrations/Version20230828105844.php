<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration automatiquement générée : Veuillez la modifier selon vos besoins !
 */
final class Version20230828105844 extends AbstractMigration
{
    public function getDescription(): string
    {
        // Retourne une description de la migration (vide dans cet exemple)
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cette migration up() est générée automatiquement, veuillez la modifier selon vos besoins
        // Elle crée une table "reservation" avec des colonnes spécifiées
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, professionel_id INT DEFAULT NULL, debut DATETIME NOT NULL, valide TINYINT(1) NOT NULL, INDEX IDX_42C8495519EB6921 (client_id), INDEX IDX_42C84955F837D6C3 (professionel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Ajoute des contraintes de clé étrangère à la table "reservation"
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955F837D6C3 FOREIGN KEY (professionel_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // Cette migration down() est générée automatiquement, veuillez la modifier selon vos besoins
        // Supprime les contraintes de clé étrangère de la table "reservation"
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519EB6921');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955F837D6C3');
        
        // Supprime la table "reservation"
        $this->addSql('DROP TABLE reservation');
    }
}
