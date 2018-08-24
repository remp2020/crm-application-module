<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;

class SimpleWidget extends BaseWidget
{
    private $templateName = 'simple_widget.latte';

    public function render($path = '', $params = '')
    {
        $widgets = $this->widgetManager->getWidgets($path);
        foreach ($widgets as $sorting => $widget) {
            if (!$this->getComponent($widget->identifier())) {
                $this->addComponent($widget, $widget->identifier());
            }
        }

        $this->template->widgets = $widgets;
        $this->template->params = $params;

        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
