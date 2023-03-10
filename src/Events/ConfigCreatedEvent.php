<?php

namespace Crm\ApplicationModule\Events;

use League\Event\AbstractEvent;
use Nette\Database\Table\ActiveRow;

class ConfigCreatedEvent extends AbstractEvent implements ConfigEventInterface
{
    public function __construct(private ActiveRow $config)
    {
    }

    public function getConfig(): ActiveRow
    {
        return $this->config;
    }
}
