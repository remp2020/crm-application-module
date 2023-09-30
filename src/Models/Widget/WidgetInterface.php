<?php

namespace Crm\ApplicationModule\Widget;

use Nette\ComponentModel\IComponent;

interface WidgetInterface extends IComponent
{
    public function header();

    public function identifier();
}
