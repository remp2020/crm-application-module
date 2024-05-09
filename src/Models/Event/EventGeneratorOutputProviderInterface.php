<?php

namespace Crm\ApplicationModule\Models\Event;

// In the next major release this interface will be merged into Crm\ApplicationModule\Models\Event\EventGeneratorInterface
interface EventGeneratorOutputProviderInterface
{
    /**
     * List of params which must be present in BeforeEvent payload from generate() method.
     *
     * @return string[]
     */
    public function getOutputParams(): array;
}
