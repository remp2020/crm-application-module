<?php

namespace Crm\ApplicationModule\Models\Widget;

interface WidgetFactoryInterface
{
    public function create(): WidgetInterface;
}
