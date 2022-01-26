<?php

namespace Crm\ApplicationModule\Components;

interface SimpleWidgetFactoryInterface
{
    /** @return SimpleWidget */
    public function create();
}
