<?php

namespace Crm\ApplicationModule\Tests;

use Nette\Bootstrap\Configurator;
use Nette\Database\Explorer;

trait RefreshContainerTrait
{
    public function refreshContainer()
    {
        if ($this->container) {
            /** @var Explorer $database */
            $database = $this->container->getByType(Explorer::class);
            $database->getConnection()->disconnect();
        }
        /** @var Configurator $configurator */
        $configurator = $GLOBALS['configurator'];
        $GLOBALS['container'] = $configurator->createContainer();
        $this->container = $GLOBALS['container'];
    }
}
