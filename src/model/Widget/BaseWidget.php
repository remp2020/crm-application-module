<?php

namespace Crm\ApplicationModule\Widget;

use Crm\ApplicationModule\Components\ListingActionWidgetFactoryInterface;
use Kdyby\Autowired\AutowireComponentFactories;
use Nette\Application\UI;
use Nette\ComponentModel\IComponent;
use Nette\DI\Helpers;
use Nette\UnexpectedValueException;

abstract class BaseWidget extends UI\Control implements WidgetInterface
{
    use AutowireComponentFactories;

    /** @var WidgetManager */
    protected $widgetManager;

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function header()
    {
        return 'base widget';
    }

    public function identifier()
    {
        return 'identifier';
    }

    protected function createComponentListingWidget(ListingActionWidgetFactoryInterface $factory)
    {
        $control = $factory->create();
        return $control;
    }

    protected function createComponent($name)
    {
        $widget = $this->widgetManager->getWidgetByIdentifier($name);
        if ($widget) {
            if (!isset($this->components[$widget->identifier()])) {
                $this->addComponent($widget, $widget->identifier());
            }
            return $widget;
        }

        $sl = $this->getComponentFactoriesLocator();

        $ucName = ucfirst($name);
        $method = 'createComponent' . $ucName;
        if ($ucName !== $name && method_exists($this, $method)) {
            $reflection = $this->getReflection()->getMethod($method);
            if ($reflection->getName() !== $method) {
                return;
            }
            $parameters = $reflection->getParameters();


            $args = [];
            if (($first = reset($parameters)) && !$first->getClass()) {
                $args[] = $name;
            }

            $args = Helpers::autowireArguments($reflection, $args, $sl);
            $component = call_user_func_array([$this, $method], $args);
            if (!$component instanceof IComponent && !isset($this->components[$name])) {
                throw new UnexpectedValueException("Method $reflection did not return or create the desired component");
            }

            return $component;
        }
    }
}
