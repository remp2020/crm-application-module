<?php

namespace Crm\ApplicationModule\Commands;

use Symfony\Component\Console\Command\Command;

interface CommandsContainerInterface
{
    /** @return boolean */
    public function registerCommand(Command $command);

    /** @return array(Command) */
    public function getCommands();

    /** @return boolean */
    public function clearCommands();
}
