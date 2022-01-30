<?php

namespace Crm\ApplicationModule;

use Nette\Database\Conventions\StaticConventions;
use Nette\Database\Explorer;

class ActiveRowFactory
{
    private Explorer $explorer;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
    }

    public function create(array $data): ActiveRow
    {
        $staticConventions = new StaticConventions();

        $selection = new Selection(
            $this->explorer,
            $staticConventions,
            'dummy'
        );

        return new ActiveRow($data, $selection);
    }
}
