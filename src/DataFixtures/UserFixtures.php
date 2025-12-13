<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Exemplaire;
use App\Entity\Ouvrage;
use App\Entity\Personne;
use App\Entity\Reservation;
use App\Enum\EtatOuvrage;
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
        $faker = Factory::create('fr_FR');

        // ======================================================
        // 1. UTILISATEURS FIXES (Pour se connecter)
        // ======================================================

        $admin = new Personne();
        $admin->setEmail('admin1@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1'));
        $manager->persist($admin);

        $librarian = new Personne();
        $librarian->setEmail('librarian1@gmail.com');
        $librarian->setRoles(['ROLE_LIBRARIAN']);
        $librarian->setPassword($this->passwordHasher->hashPassword($librarian, 'librarian1'));
        $manager->persist($librarian);

        $memberVIP = new Personne();
        $memberVIP->setEmail('member1@gmail.com');
        $memberVIP->setRoles(['ROLE_MEMBRE']);
        $memberVIP->setPassword($this->passwordHasher->hashPassword($memberVIP, 'member1'));
        $manager->persist($memberVIP);

        // Création de membres aléatoires pour peupler la base
        $randomMembers = [];
        for ($i = 0; $i < 15; $i++) {
            $m = new Personne();
            $m->setEmail($faker->unique()->email());
            $m->setRoles(['ROLE_MEMBRE']);
            $m->setPassword($this->passwordHasher->hashPassword($m, 'password'));
            $manager->persist($m);
            $randomMembers[] = $m;
        }
        // On vous ajoute à la liste des membres potentiels pour les tirages au sort
        $randomMembers[] = $memberVIP;


        // ======================================================
        // 2. AUTEURS
        // ======================================================
        $listeAuteurs = [];
        for ($i = 0; $i < 50; $i++) {
            $auteur = new Auteur();
            $auteur->setNom($faker->lastName());
            $auteur->setPrenom($faker->firstName());
            $manager->persist($auteur);
            $listeAuteurs[] = $auteur;
        }


        // ======================================================
        // 3. OUVRAGES
        // ======================================================
        $categories = ['Roman', 'Policier', 'SF', 'Fantasy', 'Biographie', 'Histoire', 'Jeunesse'];
        $tagsList = ['Best-seller', 'Coup de coeur', 'Nouveauté', 'Classique', 'Primé', 'Adapté au cinéma'];

        for ($i = 0; $i < 200; $i++) {
            $ouvrage = new Ouvrage();
            $ouvrage->setTitre($faker->catchPhrase());
            $ouvrage->setEditeur($faker->company());
            $ouvrage->setIsbn($faker->isbn13());
            $ouvrage->setAnnee((string)$faker->year());
            $ouvrage->setResume($faker->paragraph(3));
            $ouvrage->setLangues($faker->randomElement(['Français', 'Anglais', 'Espagnol']));
            $ouvrage->setCategories($faker->randomElement($categories));
            $ouvrage->setTags(implode(', ', $faker->randomElements($tagsList, 2)));

            // Auteurs (1 ou 2)
            $nbAuteurs = $faker->numberBetween(1, 2);
            for ($k = 0; $k < $nbAuteurs; $k++) {
                $ouvrage->addAuteur($faker->randomElement($listeAuteurs));
            }

            $manager->persist($ouvrage);

            // ======================================================
            // 4. EXEMPLAIRES & SITUATIONS
            // ======================================================

            // On décide aléatoirement du nombre d'exemplaires (0 à 4)
            $nbExemplaires = $faker->numberBetween(0, 4);

            for ($j = 1; $j <= $nbExemplaires; $j++) {
                $exemplaire = new Exemplaire();
                $exemplaire->setOuvrage($ouvrage);

                // Cote : 3 lettres éditeur + chiffre
                // Sécurité : si pas d'éditeur, on met 'LIB'
                $prefix = $ouvrage->getEditeur() ? substr($ouvrage->getEditeur(), 0, 3) : 'LIB';
                $cote = strtoupper($prefix) . '-' . $faker->numberBetween(10000, 99999);
                $exemplaire->setCote($cote);

                // --- MODIFICATION ICI ---
                // On utilise l'Enum au lieu des strings.
                // On n'inclut PAS 'EtatOuvrage::LOST' comme tu as demandé.
                $exemplaire->setEtat($faker->randomElement([
                    EtatOuvrage::NEW,      // Neuf
                    EtatOuvrage::GOOD,     // Bon état
                    EtatOuvrage::DAMAGED   // Abîmé
                ]));

                $exemplaire->setDisponible(true); // Par défaut

                // SCÉNARIOS D'EMPRUNT
                $scenario = $faker->numberBetween(1, 10);

                if ($scenario <= 3) {
                    // --- CAS A : LIVRE EMPRUNTÉ ---
                    $exemplaire->setDisponible(false);

                    $resa = new Reservation();
                    $resa->setOuvrage($ouvrage);
                    $resa->setExemplaire($exemplaire);

                    if ($i < 5 && $j == 1) {
                        $emprunteur = $memberVIP;
                    } else {
                        $emprunteur = $faker->randomElement($randomMembers);
                    }
                    $resa->setPersonne($emprunteur);

                    $dateEmprunt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-20 days', 'now'));
                    $resa->setDateReservation($dateEmprunt);
                    $resa->setDateRetourPrevue($dateEmprunt->modify('+30 days'));

                    $manager->persist($resa);

                } elseif ($scenario <= 5) {
                    // --- CAS B : LIVRE RENDU ---
                    $exemplaire->setDisponible(true);

                    $resa = new Reservation();
                    $resa->setOuvrage($ouvrage);
                    $resa->setExemplaire($exemplaire);
                    $resa->setPersonne($faker->randomElement($randomMembers));

                    $dateEmprunt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', '-2 months'));
                    $resa->setDateReservation($dateEmprunt);
                    $resa->setDateRetourPrevue($dateEmprunt->modify('+30 days'));
                    $resa->setDateRetourReelle($dateEmprunt->modify('+' . $faker->numberBetween(5, 35) . ' days'));

                    $manager->persist($resa);
                }

                $manager->persist($exemplaire);
            }

            // ======================================================
            // 5. LISTE D'ATTENTE (Sur certains livres)
            // ======================================================
            // Si le livre a peu d'exemplaires ou est populaire, on ajoute une file d'attente
            if ($nbExemplaires < 2 && $faker->boolean(40)) {
                $resaAttente = new Reservation();
                $resaAttente->setOuvrage($ouvrage);
                $resaAttente->setExemplaire(null); // Pas d'exemplaire = Liste d'attente
                $resaAttente->setDateRetourPrevue(null);
                $resaAttente->setDateRetourReelle(null);

                // On met VOUS en liste d'attente sur quelques livres
                if ($i > 10 && $i < 13) {
                    $attendeur = $memberVIP;
                } else {
                    $attendeur = $faker->randomElement($randomMembers);
                }
                $resaAttente->setPersonne($attendeur);

                $dateDemande = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-10 days', 'now'));
                $resaAttente->setDateReservation($dateDemande);

                $manager->persist($resaAttente);
            }
        }

        $manager->flush();
    }
}
