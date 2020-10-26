<?php

namespace Crm\ApplicationModule\Criteria\Params;

use Crm\ApplicationModule\Criteria\CriteriaParam;

class NumberParam implements CriteriaParam
{
    protected $type = 'number';

    private $key;

    private $label;

    /**
     * @var array
     */
    private $operators;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var array
     */
    private $numberInputAttributes;

    /**
     * NumberParam constructor.
     *
     * @param string     $key                   ID of the param
     * @param string     $label                 text label of the parameter
     * @param string     $unit                  text shown shown as a label of the number input
     * @param array      $operators             list of operators to select from, selected operator will be used to compare the number input with
     * @param array      $numberInputAttributes attributes passed down to <input> HTML tag (e.g. ['min' => 1, 'max' => 10, 'step' => 2]
     */
    public function __construct(string $key, string $label, string $unit, array $operators = ['='], array $numberInputAttributes = [])
    {
        $this->key = $key;
        $this->label = $label;
        $this->operators = $operators;
        $this->unit = $unit;
        $this->numberInputAttributes = $numberInputAttributes;
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
        $result = array_filter([
            'type' => $this->type(),
            'label' => $this->label(),
            'operators' => $this->operators,
            'unit' => $this->unit,
            'numberInputAttributes' => $this->numberInputAttributes,
        ]);
        return $result;
    }

    public function type(): string
    {
        return $this->type;
    }
}
