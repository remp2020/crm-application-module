<?php

namespace Crm\ApplicationModule\Models\Config;

class HermesConfig
{
    /** @var string[] */
    private array $redactedFields = [];

    /**
     * @return string[]
     */
    public function getRedactedFields(): array
    {
        return $this->redactedFields;
    }

    /**
     * @param string[] $fields
     */
    public function addRedactedFields(array $fields): void
    {
        $this->redactedFields = array_unique(array_merge($this->redactedFields, $fields));
    }
}
