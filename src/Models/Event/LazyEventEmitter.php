<?php

namespace Crm\ApplicationModule\Event;

use InvalidArgumentException;
use League\Event\CallbackListener;
use League\Event\Emitter;
use League\Event\ListenerInterface;
use Nette\DI\Container;

class LazyEventEmitter extends Emitter
{
    public function __construct(
        private Container $container
    ) {
    }

    protected function getSortedListeners($event)
    {
        if (! $this->hasListeners($event)) {
            return [];
        }

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

    public function removeListener($event, $listener)
    {
        $this->clearSortedListeners($event);
        $listeners = $this->hasListeners($event)
            ? $this->listeners[$event]
            : [];

        if (is_string($listener)) {
            $filter = function ($registered) use ($listener) {
                if (is_string($registered)) {
                    return $registered !== $listener;
                } else {
                    return $registered::class !== $listener;
                }
            };
        } else {
            $filter = function ($registered) use ($listener) {
                if (is_string($registered)) {
                    return $registered !== get_class($listener);
                } else {
                    return ! $registered->isListener($listener);
                }
            };
        }

        foreach ($listeners as $priority => $collection) {
            $listeners[$priority] = array_filter($collection, $filter);
        }

        $this->listeners[$event] = $listeners;

        return $this;
    }

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
