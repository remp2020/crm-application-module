<?php

namespace Crm\ApplicationModule;

use Closure;
use Nette\DI\Container;

interface CallbackManagerInterface
{
    public function add(Closure $callback);

    public function execAll(Container $container);
}
