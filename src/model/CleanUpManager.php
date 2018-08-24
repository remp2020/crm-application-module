<?php

namespace Crm\ApplicationModule;

use Closure;
use Nette\DI\Container;

class CleanUpManager implements CallbackManagerInterface
{
    private $callbacks = [];

    public function add(Closure $callback)
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    public function execAll(Container $container)
    {
        foreach ($this->callbacks as $callback) {
            $callback($container);
        }
    }
}
