<?php

namespace Crm\ApplicationModule\Application\Managers;

use Closure;
use Nette\DI\Container;

class CleanUpManager implements CallbackManagerInterface
{
    private $callbacks = [];

    public function add(string $key, Closure $callback)
    {
        $this->callbacks[$key] = $callback;
        return $this;
    }

    public function remove(string $key)
    {
        if (!empty($this->callbacks[$key])) {
            unset($this->callbacks[$key]);
        }
        return $this;
    }

    public function execAll(Container $container)
    {
        foreach ($this->callbacks as $callback) {
            $callback($container);
        }
    }
}
