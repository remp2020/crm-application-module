<?php

namespace Crm\ApplicationModule\Components\Graphs;

use Nette\Application\UI\Control;

abstract class BaseGraphControl extends Control
{
    protected function generateGraphId()
    {
        return md5(rand(0, 1000) . microtime() . rand(0, 1000));
    }
}
