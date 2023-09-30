<?php

namespace Crm\ApplicationModule\Event;

interface EventManagerInterface
{
    public function push(Event $event): int;

    public function shift(): ?Event;
}
