<?php

namespace Crm\ApplicationModule\Models\Event;

interface EventManagerInterface
{
    public function push(Event $event): int;

    public function shift(): ?Event;
}
