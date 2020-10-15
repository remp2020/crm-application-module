<?php

namespace Crm\ApplicationModule\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Tomaj\Hermes\Restart\RestartInterface;

class HermesShutdownCommand extends Command
{
    private $hermesRestart;

    public function __construct(RestartInterface $hermesRestart)
    {
        parent::__construct();
        $this->hermesRestart = $hermesRestart;
    }

    protected function configure()
    {
        $this->setName('application:hermes_shutdown')
            ->setDescription('Gracefully shutdowns all workers which integrate `RestartInterface`.')
            ->addOption(
                'assume-yes',
                'y',
                InputOption::VALUE_NONE,
                'Assume YES for all questions (restarts without user confirmation).'
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Preparing to shutdown all workers which integrate Hermes restart procedure.</comment>');

        // get user confirmation if assume yes wasn't used
        if (!$input->getOption('assume-yes')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Do you want to proceed with shutdown (y/N)?</question> ', false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Hermes shutdown cancelled.');
                exit(0);
            }
        }

        $output->writeln('Initiating Hermes shutdown.');
        $this->hermesRestart->restart();
        $output->writeln('<comment>Graceful shutdown of workers initiated.</comment>');
        exit(0);
    }
}
