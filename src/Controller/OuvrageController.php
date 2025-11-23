<?php

namespace App\Controller;

use App\Entity\Exemplaire;
use App\Entity\Ouvrage;
use App\Entity\Reservation;
use App\Form\ExemplaireType;
use App\Form\OuvrageFilterType;
use App\Form\OuvrageType;
use App\Repository\OuvrageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/Ouvrage')]
#[IsGranted('ROLE_LIBRARIAN')]
class OuvrageController extends AbstractController
{
    #[Route('/', name: 'app_ouvrage_index', methods: ['GET'])]
    public function index(Request $request, OuvrageRepository $ouvrageRepository): Response
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
        // Nettoyage
        $filters = array_filter($filters ?? [], function($value) { return !is_null($value) && $value !== ''; });

        $ouvrages = $ouvrageRepository->findByFilters($filters);

        return $this->render('ouvrage/index.html.twig', [
            'ouvrages' => $ouvrages,
            'formFilter' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_ouvrage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ouvrage = new Ouvrage();
        $form = $this->createForm(OuvrageType::class, $ouvrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ouvrage);
            $entityManager->flush();

            return $this->redirectToRoute('app_ouvrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrage/new.html.twig', [
            'ouvrage' => $ouvrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ouvrage_show', methods: ['GET'])]
    public function show(Ouvrage $ouvrage): Response
    {
        return $this->render('ouvrage/show.html.twig', [
            'ouvrage' => $ouvrage,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ouvrage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ouvrage $ouvrage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OuvrageType::class, $ouvrage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_ouvrage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ouvrage/edit.html.twig', [
            'ouvrage' => $ouvrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_ouvrage_delete', methods: ['GET'])]
    public function delete(Ouvrage $ouvrage, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($ouvrage);
        $entityManager->flush();
        $this->addFlash('success', 'Le livre a bien été supprimé.');
        return $this->redirectToRoute('app_ouvrage_index');
    }

    /**
     * AJOUTER UN EXEMPLAIRE (Avec gestion automatique de la file d'attente !)
     */
    #[Route('/{id}/ajouter-exemplaire', name: 'app_ouvrage_add_exemplaire', methods: ['GET', 'POST'])]
    public function addExemplaire(
        Request $request,
        Ouvrage $ouvrage,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository
    ): Response
    {
        $exemplaire = new Exemplaire();
        $exemplaire->setOuvrage($ouvrage);

        $form = $this->createForm(ExemplaireType::class, $exemplaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exemplaire);

            if ($exemplaire->isDisponible()) {

                // On regarde si quelqu'un attend ce livre
                $attente = $reservationRepository->findOneBy(
                    [
                        'ouvrage' => $ouvrage,
                        'exemplaire' => null,      // Pas encore servi
                        'dateRetourReelle' => null // Pas annulé
                    ],
                    ['dateReservation' => 'ASC']   // Le plus ancien d'abord
                );

                if ($attente) {
                    $attente->setExemplaire($exemplaire);

                    $now = new \DateTimeImmutable();
                    $attente->setDateReservation($now); // Reset de la date de début
                    $attente->setDateRetourPrevue($now->modify('+30 days'));

                    // L'exemplaire n'est plus disponible
                    $exemplaire->setDisponible(false);

                    $this->addFlash('info', 'Cet exemplaire a été automatiquement attribué à une personne sur liste d\'attente !');
                } else {
                    $this->addFlash('success', 'Nouvel exemplaire ajouté au stock (disponible).');
                }
            } else {
                $this->addFlash('success', 'Nouvel exemplaire ajouté (mais marqué comme indisponible).');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_ouvrage_show', ['id' => $ouvrage->getId()]);
        }

        return $this->render('ouvrage/add_exemplaire.html.twig', [
            'ouvrage' => $ouvrage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/exemplaire/{exemplaire_id}/edit', name: 'app_ouvrage_exemplaire_edit', methods: ['GET', 'POST'])]
    public function editExemplaire(
        Request $request,
        Ouvrage $ouvrage,
        #[MapEntity(id: 'exemplaire_id')] Exemplaire $exemplaire,
        EntityManagerInterface $entityManager
    ): Response
    {
        if ($exemplaire->getOuvrage() !== $ouvrage) {
            throw $this->createNotFoundException("Cet exemplaire n'appartient pas à l'ouvrage demandé.");
        }

        $form = $this->createForm(ExemplaireType::class, $exemplaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'exemplaire a bien été modifié.');
            return $this->redirectToRoute('app_ouvrage_show', ['id' => $ouvrage->getId()]);
        }

        return $this->render('ouvrage/edit_exemplaire.html.twig', [
            'ouvrage' => $ouvrage,
            'exemplaire' => $exemplaire,
            'form' => $form,
        ]);
    }

    #[Route('/exemplaire/{id}/delete', name: 'app_exemplaire_delete', methods: ['POST'])]
    public function deleteExemplaire(Request $request, Exemplaire $exemplaire, EntityManagerInterface $entityManager): Response
    {
        $ouvrageId = $exemplaire->getOuvrage()->getId();
        if ($this->isCsrfTokenValid('delete'.$exemplaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($exemplaire);
            $entityManager->flush();
            $this->addFlash('success', 'Exemplaire supprimé du stock.');
        }
        return $this->redirectToRoute('app_ouvrage_show', ['id' => $ouvrageId]);
    }

    #[Route('/stock-global', name: 'app_exemplaire_index', methods: ['GET'])]
    public function stockGlobal(EntityManagerInterface $entityManager): Response
    {
        $exemplaires = $entityManager->getRepository(Exemplaire::class)->findAll();
        return $this->render('exemplaire/index.html.twig', [
            'exemplaires' => $exemplaires,
        ]);
    }
}
