<?php

namespace Crm\ApplicationModule\Models\Widget;

use Nette\ComponentModel\IComponent;

interface WidgetInterface extends IComponent
{
    public function header();

    public function identifier();
}
