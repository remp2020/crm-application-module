<?php

namespace Crm\ApplicationModule\Tests\Widgets;

use Crm\ApplicationModule\Widget\BaseLazyWidget;

class TestingBWidget extends BaseLazyWidget
{
    public function header()
    {
        return 'Header B';
    }

    public function identifier()
    {
        return 'testing-b-widget';
    }
}
