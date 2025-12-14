<?php

namespace App\Scheduler\Handler;

use App\Repository\ReservationRepository;
use App\Scheduler\Message\PurgeOldDataMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PurgeHandler
{
    public function __construct(
        private ReservationRepository $repo,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    public function __invoke(PurgeOldDataMessage $message): void
    {
        $this->logger->warning("--- 🗑️ DÉBUT PURGE DES DONNÉES ---");

        // Date limite : Aujourd'hui moins 30 jours
        $limite = (new \DateTime())->modify('-30 days');

        $vieuxEmprunts = $this->repo->findOldHistory($limite);
        $count = count($vieuxEmprunts);

        foreach ($vieuxEmprunts as $resa) {
            // On supprime la ligne de réservation (l'historique)
            // Note : On ne supprime pas l'utilisateur ni le livre, juste le lien.
            $this->em->remove($resa);
        }

        $this->em->flush();

        $this->logger->warning("PURGE TERMINÉE : $count historiques supprimés.");
    }
}
