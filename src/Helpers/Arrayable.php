<?php

namespace Crm\ApplicationModule\Helpers;

/**
 * Inspired by Laravel
 */
interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array;
}
