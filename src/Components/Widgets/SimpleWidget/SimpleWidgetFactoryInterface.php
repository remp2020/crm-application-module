<?php

namespace Crm\ApplicationModule\Components\Widgets\SimpleWidget;

interface SimpleWidgetFactoryInterface
{
    public function create(): SimpleWidget;
}
