<?php

namespace Crm\ApplicationModule\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tomaj\Hermes\Dispatcher;

class HermesWorkerCommand extends Command
{
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setName('application:hermes_worker')
            ->setDescription('Hermes offline worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dispatcher->handle();
        return 0;
    }
}
