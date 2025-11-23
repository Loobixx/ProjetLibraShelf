<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Form\ConfigurationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/parametres', name: 'app_admin_settings')]
    public function settings(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. On cherche la configuration existante
        $config = $entityManager->getRepository(Configuration::class)->findOneBy([]);

        // 2. Si elle n'existe pas (première fois), on la crée avec des valeurs par défaut
        if (!$config) {
            $config = new Configuration();
            $config->setMaxLivres(3);      // Défaut : 3 livres
            $config->setDureeEmprunt(30);  // Défaut : 30 jours
            $config->setMontantPenalite(0.5); // Défaut : 50 centimes
            $entityManager->persist($config);
            $entityManager->flush();
        }

        // 3. On crée le formulaire
        $form = $this->createForm(ConfigurationType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Paramètres mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_settings');
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
