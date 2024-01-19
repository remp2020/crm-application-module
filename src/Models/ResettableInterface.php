<?php

namespace Crm\ApplicationModule\Models;

interface ResettableInterface
{
    /**
     * Reset brings the object to its initial state.
     */
    public function reset(): void;
}
