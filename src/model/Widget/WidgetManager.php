<?php

namespace Crm\ApplicationModule\Widget;

class WidgetManager implements WidgetManagerInterface
{
    private $widgets = [];

    private $overrideWidgets = [];

    private $widgetFactories = [];

    public function registerWidget($path, WidgetInterface $widget, $priority = 100, $overwrite = false)
    {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgets[$path][$priority] = $widget;
    }

    public function registerWidgetFactory($path, WidgetFactoryInterface $widgetFactory, $priority = 100, $overwrite = false)
    {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgetFactories[$path][$priority] = $widgetFactory;
    }

    public function overrideWidget($path, WidgetInterface $oldWidget, WidgetInterface $newWidget)
    {
        if (!array_key_exists($path, $this->overrideWidgets)) {
            $this->overrideWidgets[$path] = [];
        }

        $this->overrideWidgets[$path][get_class($oldWidget)] = $newWidget;
    }

    private function overrideWidgets()
    {
        if (!empty($this->overrideWidgets)) {
            foreach ($this->widgets as $path => $registeredWidgets) {
                if (isset($this->overrideWidgets[$path])) {
                    foreach ($registeredWidgets as $priority => $registeredWidget) {
                        if (array_key_exists(get_class($registeredWidget), $this->overrideWidgets[$path])) {
                            $this->widgets[$path][$priority] = $this->overrideWidgets[$path][get_class($registeredWidget)];
                            unset($this->overrideWidgets[$path][get_class($registeredWidget)]);
                        }
                    }
                }
            }
        }

        $this->overrideWidgets = [];
    }

    public function getWidgets($path)
    {
        $this->overrideWidgets();

        if (isset($this->widgets[$path])) {
            $result = $this->widgets[$path];
            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetFactories($path)
    {
        if (isset($this->widgetFactories[$path])) {
            $result = $this->widgetFactories[$path];
            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetByIdentifier($identifier)
    {
        $this->overrideWidgets();

        foreach ($this->widgets as $path => $widgets) {
            foreach ($widgets as $widget) {
                if ($widget->identifier() == $identifier) {
                    return $widget;
                }
            }
        }
        return false;
    }

    private function isPriorityAlreadyUsed($path, $priority)
    {
        return isset($this->widgets[$path][$priority]) ||
            isset($this->widgetFactories[$path][$priority]);
    }

    public function removeWidget($path, WidgetInterface $widget)
    {
        foreach ($this->widgets[$path] as $priority => $savedWidget) {
            if ($savedWidget->identifier() === $widget->identifier()) {
                unset($this->widgets[$path][$priority]);
                break;
            }
        }
    }
}
