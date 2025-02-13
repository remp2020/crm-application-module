<?php

namespace Crm\ApplicationModule\Forms;

use Contributte\FormMultiplier\Multiplier;
use Nette\Forms\Controls\BaseControl;

/**
 * @method Multiplier addMultiplier(string $name, callable $factory, int $copyNumber, int $maxCopies)
 * @method BaseControl|Container getComponent(string $name, bool $throw = true)
 * @method BaseControl|Container offsetGet(string $name, bool $throw = true)
 */
class Container extends \Nette\Forms\Container
{
    /**
     * Copy of parent implementation, exists only to guarantee that CRM's instance is returned.
     */
    public function addContainer(string|int $name): self
    {
        $control = new self;
        $control->currentGroup = $this->currentGroup;
        $this->currentGroup?->add($control);
        return $this[$name] = $control;
    }
}
