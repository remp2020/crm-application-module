<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Widget\WidgetInterface;
use Crm\ApplicationModule\Widget\WidgetManager;
use PHPUnit\Framework\TestCase;

class WidgetManagerTest extends TestCase
{
    /** @var WidgetManager */
    private $widgetManager;

    protected function setUp(): void
    {
        $this->widgetManager = new WidgetManager();
    }

    public function testRegisterAndRemoveWidget(): void
    {
        $widgetA = new class implements WidgetInterface {
            public function header()
            {
                return 'widget';
            }
            public function identifier()
            {
                return 'widgetA';
            }
        };

        $widgetB = new class implements WidgetInterface {
            public function header()
            {
                return 'widget';
            }
            public function identifier()
            {
                return 'widgetB';
            }
        };

        $this->widgetManager->registerWidget('test', $widgetA);
        $this->widgetManager->registerWidget('test', $widgetB);

        $this->assertCount(2, $this->widgetManager->getWidgets('test'));

        $this->widgetManager->removeWidget('test', $widgetA);

        $this->assertCount(1, $this->widgetManager->getWidgets('test'));
    }
}
