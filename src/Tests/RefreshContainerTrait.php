<?php

namespace Crm\ApplicationModule\Tests;

use Nette\Configurator;

trait RefreshContainerTrait
{
    public function refreshContainer()
    {
        /** @var Configurator $configurator */
        $configurator = $GLOBALS['configurator'];
        $GLOBALS['container'] = $configurator->createContainer();
        $this->container = $GLOBALS['container'];
    }
}
