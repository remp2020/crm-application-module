<?php

namespace Crm\ApplicationModule\Helpers;

use Nette\Utils\Strings;

class FilterLoader
{
    /** @var array All registered filters */
    private $filters = [];

    /**
     * Check if filter is registered, call filter if is registered
     *
     * @param string $helper
     * @return mixed
     */
    public function load(string $helper)
    {
        return $this->filters[$helper] ?? null;
    }

    public function register(string $name, callable $callback)
    {
        $this->filters[Strings::lower($name)] = $callback;
    }
}
