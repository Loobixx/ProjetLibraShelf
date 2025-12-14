<?php

namespace App\Command;

use App\Scheduler\Message\PurgeOldDataMessage;
use App\Scheduler\Message\SendRemindersMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:test-jobs',
    description: 'Lance manuellement les jobs du Scheduler (Rappels ou Purge)',
)]
class TestJobsCommand extends Command
{
    public function __construct(
        private MessageBusInterface $bus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('rappel', null, InputOption::VALUE_NONE, 'Lance le batch des rappels de livres')
            ->addOption('purge', null, InputOption::VALUE_NONE, 'Lance le batch de purge des données');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('rappel')) {
            $io->section('🚀 Lancement du batch RAPPELS...');
            // On envoie le message manuellement, comme si le Scheduler le faisait
            $this->bus->dispatch(new SendRemindersMessage());
            $io->success('Message de rappel envoyé au Handler !');
        }

        if ($input->getOption('purge')) {
            $io->section('🗑️ Lancement du batch PURGE...');
            $this->bus->dispatch(new PurgeOldDataMessage());
            $io->success('Message de purge envoyé au Handler !');
        }

        if (!$input->getOption('rappel') && !$input->getOption('purge')) {
            $io->warning('Aucune option choisie. Utilisez --rappel ou --purge');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
