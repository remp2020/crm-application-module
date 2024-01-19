<?php

namespace Crm\ApplicationModule\Application\Managers;

use Crm\ApplicationModule\model\MissingLayoutException;

class LayoutManager
{
    private $layouts = [];

    public function registerLayout($key, $path)
    {
        if (!file_exists($path)) {
            throw new MissingLayoutException("layout file doesn't exist: {$path}");
        }
        $this->layouts[$key] = $path;
    }

    public function getLayout($key): string
    {
        if (!isset($this->layouts[$key])) {
            throw new MissingLayoutException("invalid layout requested: {$key}");
        }

        return $this->layouts[$key];
    }

    public function exists($key): bool
    {
        return isset($this->layouts[$key]);
    }
}
