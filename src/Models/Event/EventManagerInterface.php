<?php

namespace Crm\ApplicationModule\Event;

interface EventManagerInterface
{
    /**
     * @param Event $event
     * @return integer
     */
    public function push(Event $event);

    /**
     * @return Event
     */
    public function shift();
}
