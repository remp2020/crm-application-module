<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Tests\Widgets\TestingAWidget;
use Crm\ApplicationModule\Tests\Widgets\TestingBWidget;
use Crm\ApplicationModule\Widget\LazyWidgetManager;
use Crm\ApplicationModule\Widget\WidgetManager;

class WidgetManagerTest extends CrmTestCase
{
    private WidgetManager $widgetManager;

    private LazyWidgetManager $lazyWidgetManager;

    private TestingAWidget $testingAWidget;

    private TestingBWidget $testingBWidget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lazyWidgetManager = clone $this->inject(LazyWidgetManager::class);
        $this->widgetManager = new WidgetManager(
            $this->lazyWidgetManager
        );

        $this->testingAWidget = new TestingAWidget(
            $this->lazyWidgetManager
        );

        $this->testingBWidget = new TestingBWidget(
            $this->lazyWidgetManager
        );
    }

    public function testRegisterWidget(): void
    {
        $this->widgetManager->registerWidget('path', $this->testingAWidget);

        $this->assertCount(1, $this->widgetManager->getWidgets('path'));
        $this->assertCount(0, $this->widgetManager->getWidgets('wrong-path'));

        $this->widgetManager->registerWidget('path', $this->testingAWidget);

        $widgets = $this->widgetManager->getWidgets('path');
        $this->assertCount(2, $widgets);
        $this->assertArrayHasKey(100, $widgets);
        $this->assertArrayHasKey(101, $widgets);

        $this->widgetManager->registerWidget('path', $this->testingAWidget, 100, true);

        $this->assertCount(2, $this->widgetManager->getWidgets('path'));
    }

    public function testGetWidgetByIdentifier(): void
    {
        $this->widgetManager->registerWidget('path', $this->testingAWidget);
        $this->widgetManager->registerWidget('path', $this->testingBWidget);

        $this->assertInstanceOf(TestingBWidget::class, $this->widgetManager->getWidgetByIdentifier('testing-b-widget'));
    }

    public function testOverrideWidget(): void
    {
        $this->widgetManager->registerWidget('path', $this->testingAWidget);
        $this->widgetManager->overrideWidget('path', $this->testingAWidget, $this->testingBWidget);

        $widgets = $this->widgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertEquals(100, array_key_first($widgets));
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));
    }

    public function testRemoveWidget(): void
    {
        $this->widgetManager->registerWidget('path', $this->testingAWidget);
        $this->widgetManager->registerWidget('path', $this->testingBWidget);
        $this->widgetManager->registerWidget('path-1', $this->testingAWidget);

        $this->assertCount(2, $this->widgetManager->getWidgets('path'));
        $this->assertCount(1, $this->widgetManager->getWidgets('path-1'));

        $this->widgetManager->removeWidget('path', $this->testingAWidget);

        $this->assertCount(1, $this->widgetManager->getWidgets('path'));
        $this->assertCount(1, $this->widgetManager->getWidgets('path-1'));
        $this->assertInstanceOf(TestingBWidget::class, current($this->widgetManager->getWidgets('path')));
    }

    public function testRemoveWidgetInitializedByLazyManager(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);
        $this->lazyWidgetManager->registerWidget('path-1', TestingAWidget::class);

        $this->assertCount(2, $this->widgetManager->getWidgets('path'));
        $this->assertCount(1, $this->widgetManager->getWidgets('path-1'));

        $this->widgetManager->removeWidget('path', $this->testingAWidget);

        $this->assertCount(1, $this->widgetManager->getWidgets('path'));
        $this->assertCount(1, $this->widgetManager->getWidgets('path-1'));
        $this->assertInstanceOf(TestingBWidget::class, current($this->widgetManager->getWidgets('path')));
    }
}
