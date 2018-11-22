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
        if (isset($this->filters[$helper])) {
            return call_user_func_array($this->filters[$helper], array_slice(func_get_args(), 1));
        }
    }

    public function register(string $name, callable $callback)
    {
        $this->filters[Strings::lower($name)] = $callback;
    }
}
