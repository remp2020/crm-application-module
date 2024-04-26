<?php

namespace Crm\ApplicationModule\Models\Widget;

use Crm\ApplicationModule\Components\Widgets\SimpleWidget\SimpleWidgetFactoryInterface;
use Crm\ApplicationModule\Models\Snippet\Control\SnippetFactory;
use Kdyby\Autowired\AutowireComponentFactories;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\DI\Resolver;
use Nette\UnexpectedValueException;

abstract class BaseWidget extends Control implements WidgetInterface
{
    use AutowireComponentFactories;

    /** @var WidgetManager */
    protected $widgetManager;

    /** @deprecated use BaseLazyWidget instead */
    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function header()
    {
        // default implementation returns classname words separated by ' '
        $classnameParts = explode('\\', static::class);
        $classname = end($classnameParts);
        $pieces = array_filter(preg_split('/(?=[A-Z])/', $classname));
        return strtolower(implode(' ', $pieces));
    }

    public function identifier()
    {
        // name of the extending class (with namespace), character '\' replaced by '_'
        return str_replace("\\", "_", static::class);
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
        $widget = $this->widgetManager->getWidgetByIdentifier($name);
        if ($widget) {
            if (!isset($this->components[$widget->identifier()])) {
                $this->addComponent($widget, $widget->identifier());
            }
            return $widget;
        }

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

        return null;
    }
}
