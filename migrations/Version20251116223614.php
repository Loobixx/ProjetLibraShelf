<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251116223614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auteur (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ouvrage (id SERIAL NOT NULL, titre VARCHAR(255) NOT NULL, editeur VARCHAR(255) NOT NULL, isbn NUMERIC(10, 0) NOT NULL, categories VARCHAR(255) NOT NULL, tags VARCHAR(255) NOT NULL, langues VARCHAR(255) NOT NULL, annee VARCHAR(255) NOT NULL, résumé VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ouvrage_auteur (ouvrage_id INT NOT NULL, auteur_id INT NOT NULL, PRIMARY KEY(ouvrage_id, auteur_id))');
        $this->addSql('CREATE INDEX IDX_3E39E6E815D884B5 ON ouvrage_auteur (ouvrage_id)');
        $this->addSql('CREATE INDEX IDX_3E39E6E860BB6FE6 ON ouvrage_auteur (auteur_id)');
        $this->addSql('ALTER TABLE ouvrage_auteur ADD CONSTRAINT FK_3E39E6E815D884B5 FOREIGN KEY (ouvrage_id) REFERENCES ouvrage (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ouvrage_auteur ADD CONSTRAINT FK_3E39E6E860BB6FE6 FOREIGN KEY (auteur_id) REFERENCES auteur (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ouvrage_auteur DROP CONSTRAINT FK_3E39E6E815D884B5');
        $this->addSql('ALTER TABLE ouvrage_auteur DROP CONSTRAINT FK_3E39E6E860BB6FE6');
        $this->addSql('DROP TABLE auteur');
        $this->addSql('DROP TABLE ouvrage');
        $this->addSql('DROP TABLE ouvrage_auteur');
    }
}
