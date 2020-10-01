<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Hermes\HermesMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tomaj\Hermes\Emitter;

class HeartbeatCommand extends Command
{
    private $emitter;

    public function __construct(Emitter $emitter)
    {
        parent::__construct();
        $this->emitter = $emitter;
    }

    protected function configure()
    {
        $this->setName('application:heartbeat')
            ->setDescription('Run heartbeat hermes worker')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->emitter->emit(new HermesMessage('heartbeat', ['executed' => time()]));
        return 0;
    }
}
