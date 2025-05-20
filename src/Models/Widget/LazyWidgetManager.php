<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Widget;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;

class LazyWidgetManager implements LazyWidgetManagerInterface
{
    private const CACHE_KEY = 'lazy_widget_identifiers';

    /**
     * @var array $widgets - key is path and value is an array of widget class names
     *  or an instances of WidgetInterface
     */
    protected array $widgets = [];

    protected array $overrideWidgets = [];

    protected array $widgetFactories = [];

    protected array $alreadyInitialized = [];

    private Container $container;

    private Storage $cacheStorage;

    public function __construct(
        Container $container,
        Storage $storage,
    ) {
        $this->container = $container;
        $this->cacheStorage = $storage;
    }

    public function registerWidget(string $path, string $widgetClassName, int $priority = 100, bool $overwrite = false): void
    {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgets[$path][$priority] = $widgetClassName;
    }

    public function registerWidgetWithInstance(string $path, WidgetInterface $widget, int $priority = 100, bool $overwrite = false): void
    {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgets[$path][$priority] = $widget;
        $this->alreadyInitialized[$widget->identifier()] = $widget;
    }

    public function registerWidgetFactory($path, string $widgetFactoryClassName, $priority = 100, $overwrite = false): void
    {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgetFactories[$path][$priority] = $widgetFactoryClassName;
    }

    public function overrideWidget(string $path, string $oldWidgetClassName, string $newWidgetClassName): void
    {
        if (!array_key_exists($path, $this->overrideWidgets)) {
            $this->overrideWidgets[$path] = [];
        }

        $this->overrideWidgets[$path][$oldWidgetClassName] = $newWidgetClassName;
    }

    private function overrideWidgets(): void
    {
        foreach ($this->overrideWidgets as $path => $overrideWidget) {
            if (isset($this->widgets[$path])) {
                foreach ($this->widgets[$path] as $priority => $registeredWidget) {
                    if (is_string($registeredWidget)) {
                        if (array_key_exists($registeredWidget, $this->overrideWidgets[$path])) {
                            $this->widgets[$path][$priority] = $this->overrideWidgets[$path][$registeredWidget];
                            unset($this->overrideWidgets[$path][$registeredWidget]);
                        }
                    } else {
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

    public function getWidgets(string $path): array
    {
        $this->overrideWidgets();

        if (isset($this->widgets[$path])) {
            $result = [];
            foreach ($this->widgets[$path] as $sorting => $widget) {
                if (is_string($widget)) {
                    /** @var WidgetInterface $widgetInstance */
                    $widgetInstance = $this->container->getByType($widget);
                    $this->alreadyInitialized[$widgetInstance->identifier()] = $widgetInstance;

                    $result[$sorting] = $widgetInstance;
                } else {
                    $result[$sorting] = $widget;
                }
            }
            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetFactories(string $path): array
    {
        if (isset($this->widgetFactories[$path])) {
            $result = [];
            foreach ($this->widgetFactories[$path] as $sorting => $widgetFactoryClassName) {
                if (isset($this->alreadyInitialized[$widgetFactoryClassName])) {
                    $result[$sorting] = $this->alreadyInitialized[$widgetFactoryClassName];
                    continue;
                }
                /** @var WidgetFactoryInterface $widgetFactory */
                $widgetFactory = $this->container->getByType($widgetFactoryClassName);
                $result[$sorting] = $widgetFactory;
                $this->alreadyInitialized[$widgetFactoryClassName] = $widgetFactory;
            }

            ksort($result);
            return $result;
        }
        return [];
    }

    public function getWidgetByIdentifier(string $identifier): WidgetInterface|null
    {
        $this->overrideWidgets();

        if (isset($this->alreadyInitialized[$identifier])) {
            return $this->alreadyInitialized[$identifier];
        }

        $cacheData = $this->cacheStorage->read(self::CACHE_KEY);
        if ($cacheData && isset($cacheData[$identifier])) {
            $className = $cacheData[$identifier];
            /** @var WidgetInterface $widgetInstance */
            $widgetInstance = $this->container->getByType($className);
            $this->alreadyInitialized[$identifier] = $widgetInstance;

            return $widgetInstance;
        }

        $cacheData = [];
        foreach ($this->widgets as $widgets) {
            foreach ($widgets as $widget) {
                if (is_string($widget)) {
                    /** @var WidgetInterface $widgetInstance */
                    $widgetInstance = $this->container->getByType($widget);
                    $this->alreadyInitialized[$widgetInstance->identifier()] = $widgetInstance;

                    $cacheData[$widgetInstance->identifier()] = $widget;
                }
            }
        }

        $this->cacheStorage->write(self::CACHE_KEY, $cacheData, [Cache::EXPIRE => 86400]);

        return $this->alreadyInitialized[$identifier] ?? null;
    }

    private function isPriorityAlreadyUsed($path, $priority): bool
    {
        return isset($this->widgets[$path][$priority]) ||
            isset($this->widgetFactories[$path][$priority]);
    }

    public function removeWidget(string $path, $widgetClassName): void
    {
        if (!isset($this->widgets[$path])) {
            return;
        }
        foreach ($this->widgets[$path] as $priority => $widget) {
            if ($widget === $widgetClassName || (is_object($widget) && get_class($widget) === $widgetClassName)) {
                unset($this->widgets[$path][$priority]);
                break;
            }
        }
    }
}
