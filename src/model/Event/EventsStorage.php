<?php

namespace Crm\ApplicationModule\Event;

class EventsStorage
{
    private $events = [];

    /**
     * @param string $code
     * @param string $event String representation of class which extends League\Event\AbstractEvent
     * @throws \Exception
     */
    public function register(string $code, string $event): void
    {
        if (!is_subclass_of($event, 'League\Event\AbstractEvent', true)) {
            throw new \Exception("Event [{$event}] must extend class League\Event\AbstractEvent.");
        }
        if (isset($this->events[$code])) {
            throw new \Exception("Code [{$code}] already in use by event {$this->events[$code]}.");
        }

        $this->events[$code] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function isEvent(string $code): bool
    {
        return in_array($code, array_keys($this->getEvents()), true);
    }
}
