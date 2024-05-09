<?php

namespace Crm\ApplicationModule\Models\Criteria;

// In the next major release this interface will be merged into Crm\ApplicationModule\Models\Criteria\ScenarioConditionModelInterface
interface ScenarioConditionModelRequirementsInterface
{
    /**
     * This method should return a list of parameters that are required for this condition model to work.
     *
     * @return string[]
     */
    public function getInputParams(): array;
}
