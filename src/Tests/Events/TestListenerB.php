<?php

namespace Crm\ApplicationModule\Tests\Events;

use League\Event\EventInterface;
use League\Event\ListenerInterface;

class TestListenerB implements ListenerInterface
{

    public function handle(EventInterface $event)
    {
    }

    public function isListener($listener)
    {
        return $this === $listener;
    }
}
