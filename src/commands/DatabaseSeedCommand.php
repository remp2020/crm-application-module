<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\SeederManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseSeedCommand extends Command
{
    private $seederManager;

    public function __construct(SeederManager $seederManager)
    {
        parent::__construct();
        $this->seederManager = $seederManager;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('application:seed')
            ->setDescription('Seed database with required values');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>***** SEED DATABASE *****</info>');
        $output->writeln('');

        foreach ($this->seederManager->getSeeders() as $seeder) {
            $className = get_class($seeder);
            $output->writeln("Seeding <info>{$className}</info>");
            $seeder->seed($output);
        }

        return 0;
    }
}
