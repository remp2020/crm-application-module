<?php

namespace Crm\ApplicationModule\Models\Widget;

interface LazyWidgetManagerInterface
{
    public function registerWidget(string $path, string $widgetClassName, int $priority = 100, bool $overwrite = false);

    public function registerWidgetWithInstance(string $path, WidgetInterface $widget, int $priority = 100, bool $overwrite = false);

    public function overrideWidget(string $path, string $oldWidgetClassName, string $newWidgetClassName);

    public function removeWidget(string $path, string $widgetClassName);

    public function registerWidgetFactory(string $path, string $widgetFactoryClassName, int $priority = 100);

    public function getWidgets(string $path);

    public function getWidgetFactories(string $path);
}
