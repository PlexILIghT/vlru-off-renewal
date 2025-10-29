<?php

namespace App\Command;

use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate-fake-data',description: 'Load Fixtures for Testing')]
class GenerateFakeDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AppFixtures $fixtures
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate fake data for the application')
            ->addOption('clear', 'c', InputOption::VALUE_NONE, 'Clear existing data before generating new data');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Loading Fixtures...');

        $this->fixtures->load($this->entityManager);

        $output->writeln('Fake data loaded.');

        return Command::SUCCESS;
    }
}
