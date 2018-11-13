<?php

namespace Crm\ApplicationModule\Widget;

interface WidgetManagerInterface
{
    public function registerWidget($path, WidgetInterface $widget, $priority = 100, $overwrite = false);

    public function overrideWidget($path, WidgetInterface $oldWidget, WidgetInterface $newWidget);

    public function registerWidgetFactory($path, WidgetFactoryInterface $widgetFactory, $priority = 100);

    public function getWidgets($path);

    public function getWidgetFactories($path);
}
