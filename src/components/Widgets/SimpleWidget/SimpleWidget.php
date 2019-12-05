<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;

/**
 * Widget used for rendering other widgets in groups.
 *
 * @package Crm\ApplicationModule\Components
 */
class SimpleWidget extends BaseWidget
{
    private $templateName = 'simple_widget.latte';

    private static $counters;

    private function getNextIdentifier(string $key)
    {
        if (!isset(self::$counters[$key])) {
            self::$counters[$key] = 0;
        }
        return $key . '_' . ++self::$counters[$key];
    }

    public function render($path = '', $params = '')
    {
        $widgets = $this->widgetManager->getWidgets($path);
        foreach ($widgets as $sorting => $widget) {
            if (!$this->getComponent($widget->identifier())) {
                $this->addComponent($widget, $widget->identifier());
            }
        }

        foreach ($this->widgetManager->getWidgetFactories($path) as $sorting => $factory) {
            $widget = $factory->create();
            $widgets[$sorting] = $widget;
            $this->addComponent($widget, $this->getNextIdentifier($widget->identifier()));
        }

        $this->template->widgets = $widgets;
        $this->template->params = $params;

        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
