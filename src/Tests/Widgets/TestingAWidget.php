<?php

namespace Crm\ApplicationModule\Tests\Widgets;

use Crm\ApplicationModule\Widget\BaseLazyWidget;

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
