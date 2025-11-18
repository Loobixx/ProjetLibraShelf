<?php
//Ce fichier permet de créé les ADMIN et les Libraire
namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Ouvrage;
use App\Entity\Personne;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin1 = new Personne();
        $admin1->setEmail('admin1@gmail.com');
        $admin1->setRoles(['ROLE_ADMIN']);
        $admin1->setPassword($this->passwordHasher->hashPassword(
            $admin1,
            'admin1'
        ));
        $manager->persist($admin1);

        $admin2 = new Personne();
        $admin2->setEmail('admin2@gmail.com');
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setPassword($this->passwordHasher->hashPassword(
            $admin2,
            'admin2'
        ));
        $manager->persist($admin2);


        $librarian = new Personne();
        $librarian->setEmail('librarian1@gmail.com');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->passwordHasher->hashPassword(
            $librarian,
            'librarian1'
        ));
        $manager->persist($librarian);


        $member = new Personne();
        $member->setEmail('member1@gmail.com');
        $member->setRoles(['ROLE_MEMBER']);
        $member->setPassword($this->passwordHasher->hashPassword(
            $member,
            'member1'
        ));
        $manager->persist($member);


        $faker = Factory::create('fr_FR');

        // === 2. CRÉER 100 FAUX AUTEURS ===
        $listeAuteurs = []; // On va les garder en mémoire
        for ($i = 0; $i < 100; $i++) {
            $auteur = new Auteur();
            $auteur->setNom($faker->lastName());   // Nom de famille au hasard
            $auteur->setPrenom($faker->firstName()); // Prénom au hasard

            $manager->persist($auteur);
            $listeAuteurs[] = $auteur; // On ajoute l'auteur créé à notre liste
        }

        // === 3. CRÉER 500 FAUX OUVRAGES ===
        $categoriesPossibles = ['Roman', 'Policier', 'Science-Fiction', 'Fantasy', 'Histoire', 'Biographie'];
        $tagsPossibles = ['Classique', 'Nouveauté', 'Best-seller', 'Aventure', 'Amour', 'Guerre'];

        for ($i = 0; $i < 500; $i++) {
            $ouvrage = new Ouvrage();

            // Utiliser Faker pour générer des fausses données
            $ouvrage->setTitre($faker->sentence(4)); // Un titre de 4 mots
            $ouvrage->setEditeur($faker->company());   // Un nom d'entreprise
            $ouvrage->setIsbn($faker->isbn13());       // Un faux ISBN
            $ouvrage->setAnnee((string)$faker->year()); // Une année au hasard (convertie en string)
            $ouvrage->setResume($faker->paragraph(3)); // Un résumé de 3 paragraphes

            // Utiliser Faker pour choisir au hasard dans nos listes
            $ouvrage->setLangues($faker->randomElement(['Français', 'Anglais', 'Espagnol']));
            $ouvrage->setCategories($faker->randomElement($categoriesPossibles));
            $ouvrage->setTags(implode(', ', $faker->randomElements($tagsPossibles, 3))); // 3 tags au hasard

            // Lier 1 à 3 auteurs au hasard depuis notre liste
            $auteursDuLivre = $faker->randomElements($listeAuteurs, $faker->numberBetween(1, 3));
            foreach ($auteursDuLivre as $auteur) {
                $ouvrage->addAuteur($auteur);
            }

            $manager->persist($ouvrage);
        }

        $manager->flush();
    }
}
