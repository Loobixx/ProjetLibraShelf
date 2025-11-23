<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Entity\Ouvrage;
use App\Entity\Reservation;
use App\Form\OuvrageFilterType;
use App\Repository\OuvrageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/Catalogue')]
#[IsGranted('ROLE_MEMBER')]
class CatalogController extends AbstractController
{
    #[Route('/', name: 'app_catalog_index')]
    public function index(Request $request, OuvrageRepository $ouvrageRepository, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OuvrageFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $filters = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        } elseif ($request->query->count() > 0) {
            $filters = $form->getData();
            $form->submit($request->query->all(), false);
        }

        $filters = array_filter($filters ?? [], fn($value) => !is_null($value) && $value !== '');

        $ouvrages = $ouvrageRepository->findByFilters($filters);

        $user = $this->getUser();
        $mesReservations = $entityManager->getRepository(Reservation::class)->findBy([
            'personne' => $user,
            'dateRetourReelle' => null
        ]);

        $statusLivresUtilisateur = [];
        foreach ($mesReservations as $resa) {
            $idLivre = $resa->getOuvrage()->getId();
            $statusLivresUtilisateur[$idLivre] = $resa->isEnAttente() ? 'attente' : 'emprunte';
        }

        return $this->render('catalog/index.html.twig', [
            'ouvrages' => $ouvrages,
            'formFilter' => $form->createView(),
            'userStatus' => $statusLivresUtilisateur,
        ]);
    }

    #[Route('/livre/{id}', name: 'app_catalog_show')]
    public function show(Ouvrage $ouvrage): Response
    {
        return $this->render('catalog/show.html.twig', [
            'ouvrage' => $ouvrage,
        ]);
    }

    #[Route('/mes-reservations', name: 'app_catalog_mes_reservations')]
    public function mesReservations(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $reservations = $entityManager->getRepository(Reservation::class)->findBy(
            ['personne' => $user],
            ['dateReservation' => 'DESC']
        );

        $emprunts = [];
        $attentes = [];
        $archives = [];

        foreach ($reservations as $resa) {
            if ($resa->isRendu()) {
                $archives[] = $resa;
            } elseif ($resa->isEnAttente()) {
                $attentes[] = $resa;
            } else {
                $emprunts[] = $resa;
            }
        }

        return $this->render('catalog/mes_reservations.html.twig', [
            'reservations' => $reservations,
            'emprunts' => $emprunts,
            'attentes' => $attentes,
            'archives' => $archives,
        ]);
    }

    #[Route('/reserver/{id}', name: 'app_catalog_reserver')]
    public function reserver(Ouvrage $ouvrage, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();

        $existingReservation = $reservationRepository->findOneBy([
            'personne' => $user,
            'ouvrage' => $ouvrage,
            'dateRetourReelle' => null
        ]);

        if ($existingReservation) {
            $this->addFlash('danger', 'Impossible : Vous avez déjà ce livre en cours ou en attente.');
            return $this->redirectToRoute('app_catalog_index');
        }

        $config = $entityManager->getRepository(Configuration::class)->findOneBy([]);
        $maxLivres = $config ? $config->getMaxLivres() : 3;
        $dureeJours = $config ? $config->getDureeEmprunt() : 30;

        $nbReservationsActives = $reservationRepository->count([
            'personne' => $user,
            'dateRetourReelle' => null
        ]);

        if ($nbReservationsActives >= $maxLivres) {
            $this->addFlash('danger', 'Limite atteinte : ' . $maxLivres . ' livres maximum.');
            return $this->redirectToRoute('app_catalog_index');
        }

        $exemplairesDispo = array_filter(
            $ouvrage->getExemplaires()->toArray(),
            fn($ex) => $ex->isDisponible()
        );

        $reservation = new Reservation();
        $reservation->setPersonne($user);
        $reservation->setOuvrage($ouvrage);

        if (!empty($exemplairesDispo)) {
            $ordreEtats = ['Neuf' => 1, 'Bon' => 2, 'Usagé' => 3, 'Endommagé' => 4, 'Hors-service' => 5];
            $meilleurExemplaire = null;
            $meilleurScore = 99;

            foreach ($exemplairesDispo as $ex) {
                $score = $ordreEtats[$ex->getEtat()] ?? 3;
                if ($score < $meilleurScore) {
                    $meilleurScore = $score;
                    $meilleurExemplaire = $ex;
                }
            }

            $meilleurExemplaire->setDisponible(false);
            $reservation->setExemplaire($meilleurExemplaire);

            $dateRetour = (new \DateTimeImmutable())->modify('+' . $dureeJours . ' days');
            $reservation->setDateRetourPrevue($dateRetour);

            $this->addFlash('success', 'Livre emprunté ! Retour prévu le ' . $dateRetour->format('d/m/Y'));
        } else {
            $this->addFlash('warning', 'Aucun exemplaire disponible. Vous êtes ajouté à la liste d’attente.');
        }

        $entityManager->persist($reservation);
        $entityManager->flush();

        return $this->redirectToRoute('app_catalog_index');
    }

    #[Route('/rendre/{id}', name: 'app_catalog_rendre')]
    public function rendre(Reservation $reservation, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository): Response
    {
        if ($reservation->getPersonne() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($reservation->isRendu()) {
            return $this->redirectToRoute('app_catalog_mes_reservations');
        }

        $reservation->setDateRetourReelle(new \DateTimeImmutable());
        $exemplaire = $reservation->getExemplaire();

        $attente = $reservationRepository->findOneBy(
            [
                'ouvrage' => $exemplaire->getOuvrage(),
                'exemplaire' => null,
                'dateRetourReelle' => null
            ],
            ['dateReservation' => 'ASC']
        );

        if ($attente) {
            $config = $entityManager->getRepository(Configuration::class)->findOneBy([]);
            $dureeJours = $config ? $config->getDureeEmprunt() : 30;

            $maintenant = new \DateTimeImmutable();
            $attente->setExemplaire($exemplaire);
            $attente->setDateReservation($maintenant);
            $attente->setDateRetourPrevue($maintenant->modify('+' . $dureeJours . ' days'));

            $exemplaire->setDisponible(false);
            $this->addFlash('info', 'Livre rendu. Transféré au prochain en liste d’attente.');
        } else {
            $exemplaire->setDisponible(true);
            $this->addFlash('success', 'Livre rendu et remis en rayon.');
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_catalog_mes_reservations');
    }

    #[Route('/annuler/{id}', name: 'app_catalog_annuler')]
    public function annuler(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getPersonne() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$reservation->isEnAttente()) {
            $this->addFlash('danger', 'Impossible d’annuler un emprunt en cours.');
            return $this->redirectToRoute('app_catalog_mes_reservations');
        }

        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation annulée.');

        return $this->redirectToRoute('app_catalog_mes_reservations');
    }
}
