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
        // 1. UTILISATEURS FIXES
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
            $titreAleatoire = ucfirst($faker->words($faker->numberBetween(2, 5), true));
            $ouvrage->setTitre($titreAleatoire);
            $ouvrage->setEditeur($faker->company());
            $ouvrage->setIsbn($faker->isbn13());
            $ouvrage->setAnnee((string)$faker->year());
            $ouvrage->setResume($faker->paragraph(3));
            $ouvrage->setLangues($faker->randomElement(['Français', 'Anglais', 'Espagnol']));
            $ouvrage->setCategories($faker->randomElement($categories));
            $ouvrage->setTags(implode(', ', $faker->randomElements($tagsList, 2)));

            $nbAuteurs = $faker->numberBetween(1, 2);
            for ($k = 0; $k < $nbAuteurs; $k++) {
                $ouvrage->addAuteur($faker->randomElement($listeAuteurs));
            }

            $manager->persist($ouvrage);

            // ======================================================
            // 4. EXEMPLAIRES & SITUATIONS
            // ======================================================

            // On décide aléatoirement du nombre d'exemplaires
            $nbExemplaires = $faker->numberBetween(0, 4);

            for ($j = 1; $j <= $nbExemplaires; $j++) {
                $exemplaire = new Exemplaire();
                $exemplaire->setOuvrage($ouvrage);

                // Cote : 3 lettres éditeur + chiffre
                $prefix = $ouvrage->getEditeur() ? substr($ouvrage->getEditeur(), 0, 3) : 'LIB';
                $cote = strtoupper($prefix) . '-' . $faker->numberBetween(10000, 99999);
                $exemplaire->setCote($cote);

                // État via Enum
                $exemplaire->setEtat($faker->randomElement([
                    EtatOuvrage::NEW,
                    EtatOuvrage::GOOD,
                    EtatOuvrage::DAMAGED
                ]));

                $exemplaire->setDisponible(true);

                // SCÉNARIOS D'EMPRUNT
                $scenario = $faker->numberBetween(1, 10);

                // --- SCENARIO 1 : RETARD IMPORTANT (20% de chance) ---
                if ($scenario <= 2) {
                    $exemplaire->setDisponible(false);

                    $resa = new Reservation();
                    $resa->setOuvrage($ouvrage);
                    $resa->setExemplaire($exemplaire);

                    // On attribue quelques retards spécifiquement à member1 pour le test
                    if ($i < 3 && $j == 1) {
                        $resa->setPersonne($memberVIP);
                    } else {
                        $resa->setPersonne($faker->randomElement($randomMembers));
                    }

                    // Emprunté il y a 60 jours
                    $dateEmprunt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-90 days', '-60 days'));
                    $resa->setDateReservation($dateEmprunt);

                    // Devait être rendu il y a 30 jours (donc RETARD)
                    $resa->setDateRetourPrevue($dateEmprunt->modify('+30 days'));
                    $resa->setDateRetourReelle(null); // Pas rendu

                    $manager->persist($resa);

                }
                // --- SCENARIO 2 : EMPRUNT EN COURS NORMAL (30% de chance) ---
                elseif ($scenario <= 5) {
                    $exemplaire->setDisponible(false);

                    $resa = new Reservation();
                    $resa->setOuvrage($ouvrage);
                    $resa->setExemplaire($exemplaire);
                    $resa->setPersonne($faker->randomElement($randomMembers));

                    // Emprunté récemment
                    $dateEmprunt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-15 days', 'now'));
                    $resa->setDateReservation($dateEmprunt);

                    // A rendre dans le futur
                    $resa->setDateRetourPrevue($dateEmprunt->modify('+30 days'));

                    $manager->persist($resa);

                }
                // --- SCENARIO 3 : LIVRE RENDU / HISTORIQUE (20% de chance) ---
                elseif ($scenario <= 7) {
                    $exemplaire->setDisponible(true);

                    $resa = new Reservation();
                    $resa->setOuvrage($ouvrage);
                    $resa->setExemplaire($exemplaire);
                    $resa->setPersonne($faker->randomElement($randomMembers));

                    // Vieux emprunts terminés
                    $dateEmprunt = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-6 months', '-2 months'));
                    $resa->setDateReservation($dateEmprunt);
                    $resa->setDateRetourPrevue($dateEmprunt->modify('+30 days'));
                    $resa->setDateRetourReelle($dateEmprunt->modify('+' . $faker->numberBetween(5, 35) . ' days'));

                    $manager->persist($resa);
                }
                // Sinon

                $manager->persist($exemplaire);
            }

            // ======================================================
            // 5. LISTE D'ATTENTE (Sur certains livres)
            // ======================================================
            if ($nbExemplaires < 2 && $faker->boolean(40)) {
                $resaAttente = new Reservation();
                $resaAttente->setOuvrage($ouvrage);
                $resaAttente->setExemplaire(null);
                $resaAttente->setDateRetourPrevue(null);
                $resaAttente->setDateRetourReelle(null);

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
