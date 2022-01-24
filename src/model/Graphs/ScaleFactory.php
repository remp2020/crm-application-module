<?php

namespace Crm\ApplicationModule\Graphs;

use Nette\Database\Explorer;

class ScaleFactory
{
    private $database;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    public function create($provider, $range)
    {
        switch ($provider) {
            case 'mysql':
                $factory = new \Crm\ApplicationModule\Graphs\Scale\Mysql\RangeScaleFactory($this->database);
                break;
            default:
                throw new \Exception("unhandled scale provider [{$provider}]");
        }
        return $factory->create($range);
    }
}
