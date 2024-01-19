<?php

namespace Crm\ApplicationModule\Models\Widget;

/**
 * @deprecated . Use Crm\ApplicationModule\Widget\LazyWidgetManager
 */
class WidgetManager implements WidgetManagerInterface
{
    private LazyWidgetManager $lazyWidgetManager;

    public function __construct(LazyWidgetManager $lazyWidgetManager)
    {
        $this->lazyWidgetManager = $lazyWidgetManager;
    }

    public function registerWidget($path, WidgetInterface $widget, $priority = 100, $overwrite = false)
    {
        $this->lazyWidgetManager->registerWidgetWithInstance($path, $widget, $priority, $overwrite);
    }

    public function registerWidgetFactory($path, WidgetFactoryInterface $widgetFactory, $priority = 100, $overwrite = false)
    {
        $this->lazyWidgetManager->registerWidgetFactory($path, get_class($widgetFactory), $priority, $overwrite);
    }

    public function overrideWidget($path, WidgetInterface $oldWidget, WidgetInterface $newWidget)
    {
        $this->lazyWidgetManager->overrideWidget($path, get_class($oldWidget), get_class($newWidget));
    }

    public function getWidgets($path)
    {
        return $this->lazyWidgetManager->getWidgets($path);
    }

    public function getWidgetFactories($path)
    {
        return $this->lazyWidgetManager->getWidgetFactories($path);
    }

    public function getWidgetByIdentifier($identifier)
    {
        return $this->lazyWidgetManager->getWidgetByIdentifier($identifier);
    }

    public function removeWidget($path, WidgetInterface $widget)
    {
        $this->lazyWidgetManager->removeWidget($path, get_class($widget));
    }
}
