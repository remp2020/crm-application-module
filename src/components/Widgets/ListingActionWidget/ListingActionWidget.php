<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;

class ListingActionWidget extends BaseWidget
{
    private $templateName = 'listing_action_widget.latte';

    private static $counter = 0;

    private function getNextIdentifier()
    {
        self::$counter++;
        return self::$counter;
    }

    public function render($path, $params)
    {
        $factories = $this->widgetManager->getWidgetFactories($path);
        $widgets = [];
        foreach ($factories as $sorting => $factory) {
            $widget = $factory->create();
            $widgets[] = $widget;
            $this->addComponent($widget, $this->getNextIdentifier());
        }

        $this->template->widgets = $widgets;
        $this->template->params = $params;

        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
