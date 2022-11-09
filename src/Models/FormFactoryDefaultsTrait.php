<?php

namespace Crm\ApplicationModule;

trait FormFactoryDefaultsTrait
{
    private array $defaults = [];

    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }
}
