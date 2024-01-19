<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\Managers\SeederManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseSeedCommand extends Command
{
    private $seederManager;

    public function __construct(SeederManager $seederManager)
    {
        parent::__construct();
        $this->seederManager = $seederManager;
    }

    protected function configure()
    {
        $this->setName('application:seed')
            ->setDescription('Seed database with required values')
            ->addOption(
                'seeder',
                's',
                InputOption::VALUE_REQUIRED,
                'Single seeder to run. Seeder has to be registered. Whole namespace is required, see usage.',
                null
            )
            ->addUsage('--seeder=Crm\\ApplicationModule\\Seeders\\ConfigsSeeder (escaped backslashes)')
            ->addUsage('--seeder="Crm\ApplicationModule\Seeders\ConfigsSeeder" (quoted string)')
            ->addUsage('-s "Crm\ApplicationModule\Seeders\ConfigsSeeder" (short option, quoted string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>***** SEED DATABASE *****</info>');
        $output->writeln('');

        $selectedSeeder = $input->getOption('seeder');
        if ($selectedSeeder === null) {
            // no seeder selected; get all registered seeders
            $seeders = $this->seederManager->getSeeders();
        } else {
            $seeders = array_filter($this->seederManager->getSeeders(), function ($seeder) use ($selectedSeeder) {
                return get_class($seeder) === $selectedSeeder;
            });
            if (count($seeders) !== 1) {
                $output->writeln("<error>Selected seeder <info> {$selectedSeeder} </info> was not found.</error>");
                return Command::FAILURE;
            }
        }

        foreach ($seeders as $seeder) {
            $className = get_class($seeder);

            $output->writeln("Seeding <info>{$className}</info>");
            $seeder->seed($output);
        }

        return Command::SUCCESS;
    }
}
