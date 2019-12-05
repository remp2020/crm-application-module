<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Widget\BaseWidget;
use Crm\ApplicationModule\Widget\WidgetManager;
use Nette\Localization\ITranslator;

/**
 * Widget used for rendering simple single stat widgets in groups.
 *
 * @package Crm\ApplicationModule\Components
 */
class SingleStatWidget extends BaseWidget
{
    private $templateName = 'single_stat_widget.latte';

    private $translator;

    public function __construct(WidgetManager $widgetManager, ITranslator $translator)
    {
        parent::__construct($widgetManager);
        $this->translator = $translator;
    }

    public function render($path, $params = [])
    {
        $widgets = $this->widgetManager->getWidgets($path);
        foreach ($widgets as $sorting => $widget) {
            if (!$this->getComponent($widget->identifier())) {
                $this->addComponent($widget, $widget->identifier());
            }
        }

        $this->template->widgets = $widgets;
        $this->template->title = $this->translator->translate($params['title']);

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . $this->templateName);
        $this->template->render();
    }
}
