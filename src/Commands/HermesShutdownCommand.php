<?php

namespace Crm\ApplicationModule\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Tomaj\Hermes\Shutdown\ShutdownInterface;

class HermesShutdownCommand extends Command
{
    private $hermesShutdown;

    public function __construct(ShutdownInterface $hermesShutdown)
    {
        parent::__construct();
        $this->hermesShutdown = $hermesShutdown;
    }

    protected function configure()
    {
        $this->setName('application:hermes_shutdown')
            ->setDescription('Gracefully shutdowns all workers which integrate `ShutdownInterface`.')
            ->addOption(
                'assume-yes',
                'y',
                InputOption::VALUE_NONE,
                'Assume YES for all questions (shutdown without user confirmation).',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Preparing to shutdown all workers which integrate Hermes shutdown procedure.</comment>');

        // get user confirmation if assume yes wasn't used
        if (!$input->getOption('assume-yes')) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Do you want to proceed with shutdown (y/N)?</question> ', false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Hermes shutdown cancelled.');
                exit(0);
            }
        }

        $output->writeln('Initiating Hermes shutdown.');
        $this->hermesShutdown->shutdown();
        $output->writeln('<comment>Graceful shutdown of workers initiated.</comment>');
        exit(0);
    }
}
