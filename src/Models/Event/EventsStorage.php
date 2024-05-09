<?php

namespace Crm\ApplicationModule\Models\Event;

use Exception;

class EventsStorage
{
    private $events = [];

    private $eventGenerators = [];

    /**
     * @param string $code
     * @param string $event String representation of class which extends League\Event\AbstractEvent
     * @param bool $isPublic Defines if event is visible outside of CRM (API calls)
     * @throws \Exception
     */
    public function register(string $code, string $event, bool $isPublic = false): void
    {
        if (!is_subclass_of($event, 'League\Event\AbstractEvent', true)) {
            throw new \Exception("Event [{$event}] must extend class League\Event\AbstractEvent.");
        }

        $def = [
            'code' => $code,
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'class' => $event,
            'is_public' => $isPublic,
        ];

        if (isset($this->events[$code])) {
            if (empty(array_diff($def, $this->events[$code]))) {
                // we're trying to register same thing here (from the tests possibly), no need to panic
                return;
            }
            throw new \Exception("Code [{$code}] already in use by event {$code} handled by {$this->events[$code]['class']}.");
        }

        $this->events[$code] = $def;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Register event's generator. Is allowed only for already registered events by register method.
     *
     * @param string $code
     * @param EventGeneratorInterface $eventGenerator
     * @throws \Exception
     */
    public function registerEventGenerator(string $code, EventGeneratorInterface $eventGenerator): void
    {
        if (!isset($this->events[$code])) {
            throw new \Exception("It is allowed to register event generator only for already registered events - event code: '{$code}'");
        }

        $this->eventGenerators[$code] = $eventGenerator;
    }

    public function getEventGenerators(): array
    {
        return $this->eventGenerators;
    }

    public function getEventGeneratorByCode(string $code): EventGeneratorInterface
    {
        if (!array_key_exists($code, $this->eventGenerators)) {
            throw new Exception(sprintf("Event generator with code '%s' doesn't exist.", $code));
        }

        return $this->eventGenerators[$code];
    }

    public function getEventsPublic(): array
    {
        return $this->getFiltered(true);
    }

    /**
     * Returns array with events filtered by event's visibility
     *
     * @param bool $public
     * @return array
     */
    private function getFiltered(bool $public = true): array
    {
        $result = [];
        foreach ($this->events as $event) {
            if ($event['is_public'] === $public) {
                $result[] = $event;
            }
        }
        return $result;
    }

    public function isEvent(string $code): bool
    {
        return in_array($code, array_keys($this->getEvents()), true);
    }

    public function isEventPublic(string $code): bool
    {
        $key = in_array($code, array_column($this->getEventsPublic(), 'code'), true);
        return $key !== false;
    }
}
