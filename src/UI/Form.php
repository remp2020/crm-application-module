<?php

namespace Crm\ApplicationModule\UI;

use Contributte\FormMultiplier\Multiplier;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * @method Multiplier addMultiplier(string $name, callable $factory, int $copyNumber, int $maxCopies)
 * @method BaseControl|Container getComponent($name)
 */
class Form extends \Nette\Application\UI\Form
{
}
