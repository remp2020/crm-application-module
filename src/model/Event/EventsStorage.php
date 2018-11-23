<?php

namespace Crm\ApplicationModule\Event;

class EventsStorage
{
    private $events = [];

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
        if (isset($this->events[$code])) {
            throw new \Exception("Code [{$code}] already in use by event {$this->events[$code]}.");
        }

        $this->events[$code] = [
            'code' => $code,
            'title' => ucfirst(str_replace('_', ' ', $code)),
            'class' => $event,
            'is_public' => $isPublic,
        ];
    }

    public function getEvents(): array
    {
        return $this->events;
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
        return in_array($code, array_keys($this->getEventsPublic()), true);
    }
}
