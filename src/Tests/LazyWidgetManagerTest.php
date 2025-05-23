<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Widget\LazyWidgetManager;
use Crm\ApplicationModule\Tests\Widgets\TestingAWidget;
use Crm\ApplicationModule\Tests\Widgets\TestingBWidget;

class LazyWidgetManagerTest extends CrmTestCase
{
    private LazyWidgetManager $lazyWidgetManager;

    private TestingAWidget $testingAWidget;

    private TestingBWidget $testingBWidget;

    public function setUp(): void
    {
        parent::setUp();

        $this->lazyWidgetManager = clone $this->inject(LazyWidgetManager::class);

        $this->testingAWidget = new TestingAWidget(
            $this->lazyWidgetManager,
        );

        $this->testingBWidget = new TestingBWidget(
            $this->lazyWidgetManager,
        );

        if (!$this->container->hasService(TestingAWidget::class)) {
            $this->container->addService(TestingAWidget::class, $this->testingAWidget);
        }
        if (!$this->container->hasService(TestingBWidget::class)) {
            $this->container->addService(TestingBWidget::class, $this->testingBWidget);
        }
    }

    public function testRegisterWidget(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);

        $this->assertCount(1, $this->lazyWidgetManager->getWidgets('path'));
        $this->assertCount(0, $this->lazyWidgetManager->getWidgets('wrong-path'));

        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(2, $widgets);
        $this->assertArrayHasKey(100, $widgets);
        $this->assertArrayHasKey(101, $widgets);

        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class, 100, true);

        $this->assertCount(2, $this->lazyWidgetManager->getWidgets('path'));
    }

    public function testGetWidgetByIdentifier(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgetByIdentifier('testing-b-widget');
        $this->assertInstanceOf(TestingBWidget::class, $widgets);
    }

    public function testOverrideWidget(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->overrideWidget('path', TestingAWidget::class, TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertEquals(100, array_key_first($widgets));
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));
    }

    public function testRemoveWidget(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);
        $this->lazyWidgetManager->registerWidget('path-1', TestingAWidget::class);

        $this->assertCount(2, $this->lazyWidgetManager->getWidgets('path'));
        $this->assertCount(1, $this->lazyWidgetManager->getWidgets('path-1'));

        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $this->assertCount(1, $this->lazyWidgetManager->getWidgets('path'));
        $this->assertCount(1, $this->lazyWidgetManager->getWidgets('path-1'));
        $this->assertInstanceOf(TestingBWidget::class, current($this->lazyWidgetManager->getWidgets('path')));
    }

    public function testDeferredRemoval(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);

        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');

        $this->assertCount(1, $widgets);
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));

        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class, 300);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(2, $widgets);
    }

    public function testPreventReregistration(): void
    {
        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));
    }

    public function testOutOfOrderInitialization(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);

        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $this->lazyWidgetManager->registerWidget('path', TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));

        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(2, $widgets);
    }

    public function testRemovalOfInstanceWidget(): void
    {
        $this->lazyWidgetManager->registerWidgetWithInstance('path', $this->testingAWidget);
        $this->lazyWidgetManager->registerWidgetWithInstance('path', $this->testingBWidget);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(2, $widgets);

        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));

        $this->lazyWidgetManager->removeWidget('path', TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertEmpty($widgets);
    }

    public function testRemovalBeatsLateOverride(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->removeWidget('path', TestingAWidget::class);

        $this->lazyWidgetManager->overrideWidget('path', TestingAWidget::class, TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertEmpty($widgets, 'Removed widget must stay removed even after override');
    }

    public function testRemoveOverriddenWidget(): void
    {
        $this->lazyWidgetManager->registerWidget('path', TestingAWidget::class);
        $this->lazyWidgetManager->overrideWidget('path', TestingAWidget::class, TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertCount(1, $widgets);
        $this->assertInstanceOf(TestingBWidget::class, current($widgets));

        $this->lazyWidgetManager->removeWidget('path', TestingBWidget::class);

        $widgets = $this->lazyWidgetManager->getWidgets('path');
        $this->assertEmpty($widgets, 'Overridden widget should be successfully removed');
    }
}
