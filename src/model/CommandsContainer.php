<?php

namespace Crm\ApplicationModule\Commands;

use Symfony\Component\Console\Command\Command;

class CommandsContainer implements CommandsContainerInterface
{
    private $commands = [];

    public function registerCommand(Command $command)
    {
        $this->commands[] = $command;
        return true;
    }

    /**
     * @return array(Command)
     */
    public function getCommands()
    {
        return $this->commands;
    }

    public function clearCommands()
    {
        $this->commands = [];
        return true;
    }
}
