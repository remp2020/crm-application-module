<?php

namespace Crm\ApplicationModule\Models\Criteria\ScenarioParams;

use Crm\ApplicationModule\Models\Criteria\ScenarioCriteriaParamInterface;

class StringLabeledArrayParam implements ScenarioCriteriaParamInterface
{
    protected string $type = 'string_labeled_array';

    private array $options;

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
    public function __construct(
        private string $key,
        private string $label,
        array $options,
        private string $operator = 'or',
        private bool $freeSolo = false,
    ) {
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
        return [
            'key' => $this->key(),
            'type' => $this->type(),
            'label' => $this->label(),
            'options' => $this->options,
            'operator' => $this->operator,
            'freeSolo' => $this->freeSolo,
        ];
    }

    public function type(): string
    {
        return $this->type;
    }
}
