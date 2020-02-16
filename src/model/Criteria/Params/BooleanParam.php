<?php

namespace Crm\ApplicationModule\Criteria\Params;

use Crm\ApplicationModule\Criteria\CriteriaParam;

class BooleanParam implements CriteriaParam
{
    protected $type = 'boolean';

    private $key;

    private $label;

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function blueprint(): array
    {
        $result = [
            'type' => $this->type(),
            'label' => $this->label(),
        ];
        return $result;
    }

    public function type(): string
    {
        return $this->type;
    }
}
