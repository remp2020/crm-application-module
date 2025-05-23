<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Widget;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;

class LazyWidgetManager implements LazyWidgetManagerInterface
{
    /** @var string */
    private const CACHE_KEY = 'lazy_widget_identifiers';

    /** @var int */
    private const CACHE_TTL = 86_400;

    /**
     * Key is path (placeholder) where widget is displayed, value is array where
     * int key is priority and value is widget class name or WidgetInterface instance.
     *
     * @var array<string,array<int,string|WidgetInterface>>
     */
    protected array $widgets = [];

    /** @var array<string,array<string,string>> */
    protected array $overrideWidgets = [];

    /** @var array<string,array<string,bool>> */
    protected array $removeWidgets = [];

    /** @var array<string,array<int,string>> */
    protected array $widgetFactories = [];

    /** @var array<string,WidgetInterface> */
    protected array $alreadyInitialized = [];

    public function __construct(
        private readonly Container $container,
        private readonly Storage $cacheStorage,
    ) {
    }

    public function registerWidget(
        string $path,
        string $widgetClassName,
        int $priority = 100,
        bool $overwrite = false,
    ): void {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgets[$path][$priority] = $widgetClassName;
    }

    public function registerWidgetWithInstance(
        string $path,
        WidgetInterface $widget,
        int $priority = 100,
        bool $overwrite = false,
    ): void {
        if ($this->isPriorityAlreadyUsed($path, $priority) && !$overwrite) {
            do {
                $priority++;
            } while ($this->isPriorityAlreadyUsed($path, $priority));
        }
        $this->widgets[$path][$priority] = $widget;
        $this->alreadyInitialized[$widget->identifier()] = $widget;
    }

    public function registerWidgetFactory(
        $path,
        string $widgetFactoryClassName,
        $priority = 100,
        $overwrite = false,
    ): void {
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
                    $class = is_string($registeredWidget) ? $registeredWidget : $registeredWidget::class;
                    if (array_key_exists($class, $this->overrideWidgets[$path])) {
                        $this->widgets[$path][$priority] = $this->overrideWidgets[$path][$class];
                        unset($this->overrideWidgets[$path][$class]);
                    }
                }
            }
        }

        $this->overrideWidgets = [];
    }

    private function removeWidgets(): void
    {
        foreach ($this->removeWidgets as $path => $removals) {
            if (!isset($this->widgets[$path])) {
                continue;
            }
            foreach ($this->widgets[$path] as $priority => $registeredWidget) {
                $class = is_string($registeredWidget) ? $registeredWidget : $registeredWidget::class;
                if (isset($removals[$class])) {
                    unset($this->widgets[$path][$priority]);
                }
            }
        }

        $this->removeWidgets = [];
    }

    public function getWidgets(string $path): array
    {
        $this->removeWidgets();
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

    public function getWidgetByIdentifier(string $identifier): ?WidgetInterface
    {
        $this->removeWidgets();
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

        $this->cacheStorage->write(self::CACHE_KEY, $cacheData, [Cache::Expire => self::CACHE_TTL]);

        return $this->alreadyInitialized[$identifier] ?? null;
    }

    private function isPriorityAlreadyUsed($path, $priority): bool
    {
        return isset($this->widgets[$path][$priority]) ||
            isset($this->widgetFactories[$path][$priority]);
    }

    public function removeWidget(string $path, string $widgetClassName): void
    {
        if (!array_key_exists($path, $this->removeWidgets)) {
            $this->removeWidgets[$path] = [];
        }

        $this->removeWidgets[$path][$widgetClassName] = true;
    }
}
