<?php

namespace Crm\ApplicationModule\Events;

use League\Event\AbstractEvent;

class FrontendRequestEvent extends AbstractEvent
{
    private array $flashMessages = [];

    public function addFlashMessages(string $message, string $type = 'info'): array
    {
        return $this->flashMessages[] = ['message' => $message, 'type' => $type];
    }

    public function getFlashMessages(): array
    {
        return $this->flashMessages;
    }
}
