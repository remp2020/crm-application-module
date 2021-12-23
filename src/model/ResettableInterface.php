<?php

namespace Crm\ApplicationModule;

interface ResettableInterface
{
    /**
     * Reset brings the object to its initial state.
     */
    public function reset(): void;
}
