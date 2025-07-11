<?php

namespace Crm\ApplicationModule\Models\Widget;

interface DetailWidgetInterface extends WidgetInterface
{
    public function header(string $id = ''): string;
}
