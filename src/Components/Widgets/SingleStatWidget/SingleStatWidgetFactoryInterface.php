<?php

namespace Crm\ApplicationModule\Components\Widgets\SingleStatWidget;

interface SingleStatWidgetFactoryInterface
{
    public function create(): SingleStatWidget;
}
