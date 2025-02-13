<?php

namespace Crm\ApplicationModule\Application;

use Symfony\Component\Console\Command\Command;

interface CommandsContainerInterface
{
    /** @return boolean */
    public function registerCommand(Command $command);

    /** @return Command[] */
    public function getCommands();

    /** @return boolean */
    public function clearCommands();
}
