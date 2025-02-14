<?php

namespace Crm\ApplicationModule\UI;

use Contributte\FormMultiplier\Multiplier;
use Crm\ApplicationModule\Forms\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * @method Multiplier addMultiplier(string $name, callable $factory, int $copyNumber, int $maxCopies)
 * @method BaseControl|Container getComponent(string $name, bool $throw = true)
 * @method BaseControl|Container offsetGet(string $name, bool $throw = true)
 */
class Form extends \Nette\Application\UI\Form
{
    /**
     * Copy of parent implementation, exists only to guarantee that CRM's instance is returned.
     */
    public function addContainer(string|int $name): Container
    {
        $control = new Container();
        $control->currentGroup = $this->currentGroup;
        $this->currentGroup?->add($control);
        return $this[$name] = $control;
    }
}
