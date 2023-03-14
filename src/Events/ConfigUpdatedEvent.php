<?php

namespace Crm\ApplicationModule\Events;

use League\Event\AbstractEvent;
use Nette\Database\Table\ActiveRow;

class ConfigUpdatedEvent extends AbstractEvent
{
    public function __construct(private ActiveRow $config, private ?string $originalValue)
    {
    }

    public function getConfig(): ActiveRow
    {
        return $this->config;
    }

    public function getOriginalValue(): string
    {
        return $this->originalValue;
    }
}
