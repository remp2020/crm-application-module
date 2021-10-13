<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\CallbackManagerInterface;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    /** @var CallbackManagerInterface  */
    private $cleanUpManager;

    /** @var Container  */
    private $container;

    public function __construct(CallbackManagerInterface $cleanUpManager, Container $container)
    {
        parent::__construct();
        $this->cleanUpManager = $cleanUpManager;
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setName('application:cleanup')
            ->setDescription('Cleanup old data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cleanUpManager->execAll($this->container);
        return Command::SUCCESS;
    }
}
