<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117215132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exemplaire ADD ouvrage_id INT NOT NULL');
        $this->addSql('ALTER TABLE exemplaire ADD cote VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE exemplaire ADD etat VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE exemplaire ADD CONSTRAINT FK_5EF83C9215D884B5 FOREIGN KEY (ouvrage_id) REFERENCES ouvrage (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5EF83C923DD722C9 ON exemplaire (cote)');
        $this->addSql('CREATE INDEX IDX_5EF83C9215D884B5 ON exemplaire (ouvrage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE exemplaire DROP CONSTRAINT FK_5EF83C9215D884B5');
        $this->addSql('DROP INDEX UNIQ_5EF83C923DD722C9');
        $this->addSql('DROP INDEX IDX_5EF83C9215D884B5');
        $this->addSql('ALTER TABLE exemplaire DROP ouvrage_id');
        $this->addSql('ALTER TABLE exemplaire DROP cote');
        $this->addSql('ALTER TABLE exemplaire DROP etat');
    }
}
