<?php

namespace App\Scheduler\Handler;

use App\Repository\ReservationRepository;
use App\Scheduler\Message\SendRemindersMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BatchReminderHandler
{
    public function __construct(
        private ReservationRepository $repo,
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    public function __invoke(SendRemindersMessage $message): void
    {
        $today = new \DateTime();
        $this->logger->info("--- 📧 DÉBUT BATCH RAPPELS ---");

        // 1. Rappel J-3 (Bientôt à rendre)
        $dateJ3 = (clone $today)->modify('+3 days');
        $this->processBatch($this->repo->findDueAt($dateJ3), 'J-3 : Bientôt à rendre');

        // 2. Rappel J0 (Aujourd'hui)
        $this->processBatch($this->repo->findDueAt($today), 'J0 : C\'est pour aujourd\'hui !');

        // 3. Rappel J+7 (Retard critique)
        $datePlus7 = (clone $today)->modify('-7 days');
        $this->processBatch($this->repo->findDueAt($datePlus7), 'J+7 : RETARD IMPORTANT');

        $this->logger->info("--- FIN BATCH RAPPELS ---");
    }

    private function processBatch(array $reservations, string $sujet): void
    {
        foreach ($reservations as $resa) {
            $emailUser = $resa->getPersonne()->getEmail();

            $email = (new Email())
                ->from('biblio@librashelf.com')
                ->to($emailUser)
                ->subject($sujet)
                ->text("Rappel pour le livre : " . $resa->getOuvrage()->getTitre());

            // Envoi asynchrone (si configuré dans messenger.yaml)
            $this->mailer->send($email);

            $this->logger->info("Mail envoyé à $emailUser ($sujet)");
        }
    }
}
