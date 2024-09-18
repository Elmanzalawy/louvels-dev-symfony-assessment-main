<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CountrySyncService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CountrySyncCommand extends Command
{
    public function __construct(
        private CountrySyncService $countrySyncService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('countries:sync');
        $this->setDescription('Synchronize the countries');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->countrySyncService->syncCountries();
            $io->success('Countries have been successfully synced.');
        } catch (\Exception $e) {
            $io->error('Error syncing countries: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
