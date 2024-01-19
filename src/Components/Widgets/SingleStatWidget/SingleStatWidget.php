<?php

namespace Crm\ApplicationModule\Components\Widgets\SingleStatWidget;

use Crm\ApplicationModule\Widget\BaseLazyWidget;
use Crm\ApplicationModule\Widget\LazyWidgetManager;
use Nette\Localization\Translator;

/**
 * Widget used for rendering simple single stat widgets in groups.
 *
 * @package Crm\ApplicationModule\Components
 */
class SingleStatWidget extends BaseLazyWidget
{
    private $templateName = 'single_stat_widget.latte';

    private $translator;

    public function __construct(
        Translator $translator,
        LazyWidgetManager $lazyWidgetManager
    ) {
        parent::__construct($lazyWidgetManager);

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
