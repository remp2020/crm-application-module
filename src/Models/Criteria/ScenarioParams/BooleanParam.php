<?php

namespace Crm\ApplicationModule\Models\Criteria\ScenarioParams;

use Crm\ApplicationModule\Scenarios\ScenarioCriteriaParamInterface;

class BooleanParam implements ScenarioCriteriaParamInterface
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
            'key' => $this->key(),
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
