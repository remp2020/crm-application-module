<?php

namespace Crm\ApplicationModule\Tests\Widgets;

use Crm\ApplicationModule\Models\Widget\BaseLazyWidget;

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
