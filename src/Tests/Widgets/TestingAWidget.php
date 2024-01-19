<?php

namespace Crm\ApplicationModule\Tests\Widgets;

use Crm\ApplicationModule\Models\Widget\BaseLazyWidget;

class TestingAWidget extends BaseLazyWidget
{
    public function header()
    {
        return 'Header A';
    }

    public function identifier()
    {
        return 'testing-a-widget';
    }
}
