<?php

namespace Crm\ApplicationModule\Models\Criteria;

use Crm\ApplicationModule\Models\Database\Selection;

interface ScenarioConditionModelInterface
{
    /**
     * This method should return database Selection with filtered row
     * which will be used as base selection for applying scenario conditions.
     *
     * @param $scenarioJobParameters array of scenario job parameters fetched from scenarios_jobs->parameters database column
     * @return Selection
     */
    public function getItemQuery($scenarioJobParameters): Selection;
}
