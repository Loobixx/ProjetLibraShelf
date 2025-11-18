<?php

namespace App\Controller;

use App\Entity\Ouvrage;
use App\Form\OuvrageType;
use App\Repository\OuvrageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ouvrage')]
final class OuvrageController extends AbstractController
{
    #[Route(name: 'app_ouvrage_index', methods: ['GET'])]
    public function index(OuvrageRepository $ouvrageRepository): Response
    {
        return $this->render('ouvrage/index.html.twig', [
            'ouvrages' => $ouvrageRepository->findAll(),
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

    #[Route('/{id}', name: 'app_ouvrage_delete', methods: ['POST'])]
    public function delete(Request $request, Ouvrage $ouvrage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ouvrage->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ouvrage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ouvrage_index', [], Response::HTTP_SEE_OTHER);
    }
}
