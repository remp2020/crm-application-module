<?php

namespace Crm\ApplicationModule\Widget;

class WidgetManager implements WidgetManagerInterface
{
    private $widgets = [];

    private $widgetsFactories = [];

    public function registerWidget($path, WidgetInterface $widget, $priority = 100, $overwrite = false)
    {
        if (isset($this->widgets[$path][$priority]) && !$overwrite) {
            do {
                $priority++;
            } while (isset($this->widgets[$path][$priority]));
        }
        $this->widgets[$path][$priority] = $widget;
    }

    public function deregisterWidget($path, WidgetInterface $widget)
    {
        if (isset($this->widgets[$path])) {
            foreach ($this->widgets[$path] as $priority => $w) {
                if (get_class($widget) === get_class($w)) {
                    unset($this->widgets[$path][$priority]);
                }
            }
        }
    }

    public function registerWidgetFactory($path, WidgetFactoryInterface $widgetFactory, $priority = 100)
    {
        $this->widgetsFactories[$path][$priority] = $widgetFactory;
    }

    public function getWidgets($path)
    {
        if (isset($this->widgets[$path])) {
            $result = $this->widgets[$path];
            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetFactories($path)
    {
        if (isset($this->widgetsFactories[$path])) {
            $result = $this->widgetsFactories[$path];
            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetByIdentifier($identifier)
    {
        foreach ($this->widgets as $path => $widgets) {
            foreach ($widgets as $widget) {
                if ($widget->identifier() == $identifier) {
                    return $widget;
                }
            }
        }
        return false;
    }
}
