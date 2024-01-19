<?php

namespace Crm\ApplicationModule\Models\Criteria\ScenarioParams;

use Crm\ApplicationModule\Models\Criteria\ScenarioCriteriaParamInterface;

class TimeframeParam implements ScenarioCriteriaParamInterface
{
    protected $type = 'timeframe';

    /** @var string */
    private $key;

    /** @var string */
    private $label;

    /** @var string */
    private $amountLabel;

    /** @var string */
    private $unitsLabel;

    /** @var array */
    private $operators;

    /** @var array */
    private $units;

    /** @var array */
    private $amountInputAttributes;

    /**
     * DateTimeParam constructor.
     *
     * @param string    $key                        ID of the param
     * @param string    $label                      text label of the parameter
     * @param string    $amountLabel                text shown shown as a label of the amount input
     * @param string    $unitsLabel                 text shown shown as a label of the unit input
     * @param array     $operators                  list of operators to select from; selected operator will be used to compare the number input with
     * @param array     $units                      list of timeframe units to select from; selected unit will be used with amount (eg. hours, days, months)
     * @param array     $amountInputAttributes      attributes passed down to <input> HTML tag (e.g. ['min' => 1, 'max' => 10, 'step' => 2]
     */
    public function __construct(
        string $key,
        string $label,
        string $amountLabel = 'Amount',
        string $unitsLabel = 'Units',
        array $operators = ['=', '>', '<', '>=', '<='],
        array $units = ['days', 'months', 'years'],
        array $amountInputAttributes = ['min' => 0]
    ) {
        $this->key = $key;
        $this->label = $label;
        $this->amountLabel = $amountLabel;
        $this->unitsLabel = $unitsLabel;
        $this->operators = $operators;
        $this->units = $units;
        $this->amountInputAttributes = $amountInputAttributes;
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
            'key' => $this->key(),
            'type' => $this->type(),
            'label' => $this->label(),
            'amountLabel' => $this->amountLabel,
            'unitsLabel' => $this->unitsLabel,
            'operators' => $this->operators,
            'units' => $this->units,
            'amountInputAttributes' => $this->amountInputAttributes,
        ]);
        return $result;
    }

    public function type(): string
    {
        return $this->type;
    }
}
