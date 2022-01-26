<?php

namespace Crm\ApplicationModule\Event;

use DateInterval;

interface EventGeneratorInterface
{
    /**
     * @param DateInterval $timeOffset
     *
     * @return BeforeEvent[]
     */
    public function generate(DateInterval $timeOffset): array;
}
