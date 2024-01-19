<?php

namespace Crm\ApplicationModule\Models\Widget;

use Crm\ApplicationModule\Components\Widgets\SimpleWidget\SimpleWidgetFactoryInterface;
use Crm\ApplicationModule\Models\Snippet\Control\SnippetFactory;
use Kdyby\Autowired\AutowireComponentFactories;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Resolver;
use Nette\UnexpectedValueException;

abstract class BaseLazyWidget extends Control implements WidgetInterface
{
    use AutowireComponentFactories;

    protected LazyWidgetManager $widgetManager;

    public function __construct(LazyWidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function header()
    {
        return 'base lazy widget';
    }

    public function identifier()
    {
        return 'identifier';
    }

    protected function createComponentSimpleWidget(SimpleWidgetFactoryInterface $factory)
    {
        $control = $factory->create();
        return $control;
    }

    protected function createComponentSnippet(SnippetFactory $factory)
    {
        $control = $factory->create();
        return $control;
    }

    protected function createComponent(string $name): ?IComponent
    {
        $ucName = ucfirst($name);
        $method = 'createComponent' . $ucName;
        if ($ucName !== $name && method_exists($this, $method)) {
            $reflection = $this->getReflection()->getMethod($method);
            if ($reflection->getName() !== $method) {
                return null;
            }
            $parameters = $reflection->getParameters();


            $args = [];
            if (($first = reset($parameters)) && !$first->getType()) {
                $args[] = $name;
            }

            $getter = function (string $type) {
                return $this->getComponentFactoriesLocator()->getByType($type);
            };
            $args = Resolver::autowireArguments($reflection, $args, $getter);
            $component = call_user_func_array([$this, $method], $args);
            if (!$component instanceof IComponent && !isset($this->components[$name])) {
                throw new UnexpectedValueException("Method $reflection did not return or create the desired component");
            }

            return $component;
        }

        $widget = $this->widgetManager->getWidgetByIdentifier($name);
        if ($widget) {
            if (!isset($this->components[$widget->identifier()])) {
                $this->addComponent($widget, $widget->identifier());
            }
            return $widget;
        }

        return null;
    }
}
