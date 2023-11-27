<?php

namespace Crm\ApplicationModule\Event;

use InvalidArgumentException;
use League\Event\CallbackListener;
use League\Event\Emitter;
use League\Event\ListenerInterface;
use Nette\DI\Container;

class LazyEventEmitter extends Emitter
{
    private array $listenersToRemove = [];

    public function __construct(
        private Container $container
    ) {
    }

    /**
     * @param string $event
     * @param ListenerInterface|callable|string $listener
     * @param int $priority
     * @return $this
     */
    public function addListener($event, $listener, $priority = self::P_NORMAL)
    {
        return parent::addListener($event, $listener, $priority);
    }

    protected function getSortedListeners($event)
    {
        if (!$this->hasListeners($event)) {
            return [];
        }

        // remove listeners which were stored for removal
        $this->processListenersToRemove($event);

        $listeners = $this->listeners[$event];
        krsort($listeners);

        foreach ($listeners as &$samePriorityListeners) {
            foreach ($samePriorityListeners as &$listener) {
                if (is_string($listener)) {
                    $listener = $this->container->getByType($listener);
                }
            }
        }

        return call_user_func_array('array_merge', $listeners);
    }

    /**
     * @param string $event
     * @param ListenerInterface|callable|string $listener
     * @return $this
     */
    public function removeListener($event, $listener)
    {
        $this->listenersToRemove[$event][] = $listener;
        return $this;
    }

    public function removeAllListeners($event)
    {
        parent::removeAllListeners($event);

        // all listeners of event were removed; clear also listeners queued for removal
        if (isset($this->listenersToRemove[$event])) {
            unset($this->listenersToRemove[$event]);
        }

        return $this;
    }

    private function processListenersToRemove($event): self
    {
        if (empty($this->listenersToRemove[$event])) {
            return $this;
        }

        $this->clearSortedListeners($event);

        $listeners = $this->hasListeners($event)
            ? $this->listeners[$event]
            : [];
        if (empty($listeners)) {
            return $this;
        }

        foreach ($this->listenersToRemove[$event] as $listener) {
            if (is_string($listener)) {
                $filter = function ($registered) use ($listener) {
                    if (is_string($registered)) {
                        return $registered !== $listener;
                    }
                    return $registered::class !== $listener;
                };
            } else {
                $filter = function ($registered) use ($listener) {
                    if (is_string($registered)) {
                        return $registered !== get_class($listener);
                    }
                    return !($registered->isListener($listener));
                };
            }

            foreach ($listeners as $priority => $collection) {
                $listeners[$priority] = array_filter($collection, $filter);
            }
        }

        $this->listeners[$event] = $listeners;

        return $this;
    }

    /**
     * @param ListenerInterface|callable|string $listener
     * @return callable|CallbackListener|ListenerInterface|string
     */
    protected function ensureListener($listener)
    {
        if ($listener instanceof ListenerInterface) {
            return $listener;
        }

        if (is_callable($listener)) {
            return CallbackListener::fromCallable($listener);
        }

        if (is_string($listener)) {
            return $listener;
        }

        throw new InvalidArgumentException('Listeners should be ListenerInterface, Closure, callable or string. Received type: '.gettype($listener));
    }
}
