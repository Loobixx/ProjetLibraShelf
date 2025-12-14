<?php

namespace App\Scheduler\Handler;

use App\Repository\ReservationRepository;
use App\Scheduler\Message\CheckLateLoansMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CheckLateLoansHandler
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private LoggerInterface $logger
    ) {}

    public function __invoke(CheckLateLoansMessage $message): void
    {
        $this->logger->info('⏰ SCHEDULER: Début de la vérification des retards...');

        // On cherche les livres non rendus (dateRetourReelle est NULL)
        // et dont la date prévue est PASSÉE (< NOW)
        $lateReservations = $this->reservationRepository->findLateReturns();

        $count = count($lateReservations);

        if ($count > 0) {
            foreach ($lateReservations as $resa) {
                $this->logger->warning(sprintf(
                    'RETARD: Le livre "%s" emprunté par %s devait être rendu le %s.',
                    $resa->getOuvrage()->getTitre(),
                    $resa->getPersonne()->getEmail(),
                    $resa->getDateRetourPrevue()->format('d/m/Y')
                ));
            }
        } else {
            $this->logger->info('✅ Aucun retard détecté aujourd\'hui.');
        }
    }
}
