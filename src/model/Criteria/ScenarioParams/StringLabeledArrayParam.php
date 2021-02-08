<?php

namespace Crm\ApplicationModule\Criteria\ScenarioParams;

use Crm\ApplicationModule\Scenarios\ScenarioCriteriaParamInterface;

class StringLabeledArrayParam implements ScenarioCriteriaParamInterface
{
    protected $type = 'string_labeled_array';

    private $key;

    private $label;

    private $options;

    private $operator;

    private $freeSolo;

    /**
     * Constructor.
     *
     * @param string $key Identifier
     * @param string $label Human description of parameter
     * @param array  $options Options to select from, may contain:
     *                         - simple key-value pairs (['svk' => 'Slovakia'])
     *                         - key-value pairs with additional attributes (mandatory: label; optional: group, subtitle)
     *                           e.g. ['svk' => ['label' => 'Slovakia','group' => 'Europe', 'subtitle'=>'(Good Idea Slovakia)']]
     * @param string $operator Operator applied between selected values (and/or)
     * @param bool   $freeSolo If enabled, allow values outside of provided options
     */
    public function __construct(string $key, string $label, array $options, $operator = 'or', $freeSolo = false)
    {
        $this->options = array_map(function ($value) use ($options) {
            if (is_array($options[$value])) {
                return array_filter([
                    'value' => $value,
                    'label' => $options[$value]['label'], // Label is required
                    'subtitle' => $options[$value]['subtitle'] ?? null, // Subtitle (text shown after label) is optional
                    'group' => $options[$value]['group'] ?? null, // Group is optional
                ]);
            }

            return (object) [
                'value' => $value,
                'label' => $options[$value],
            ];
        }, array_keys($options));
        $this->key = $key;
        $this->label = $label;
        $this->operator = $operator;
        $this->freeSolo = $freeSolo;
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
            'options' => $this->options,
            'operator' => $this->operator,
            'freeSolo' => $this->freeSolo,
        ];
        return $result;
    }

    public function type(): string
    {
        return $this->type;
    }
}
