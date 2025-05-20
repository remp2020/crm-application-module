<?php

namespace Crm\ApplicationModule\Models\Database;

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
            'dummy',
        );

        return new ActiveRow($data, $selection);
    }

    /**
     * @return ActiveRow[]
     */
    public function createMultiple(array $data): array
    {
        return array_map(
            fn (array $data) => $this->create($data),
            $data,
        );
    }
}
