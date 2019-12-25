<?php

namespace Crm\ApplicationModule\Criteria;

use Nette\Database\Table\Selection;

interface ScenariosCriteriaInterface
{
    /**
     * params returns an array of CriteriaParam definitions available for the Criteria.
     *
     * The generator and UI currently support following type of parameters:
     *
     *   * StringParam
     *   * StringArrayParam
     *   * NumberParam
     *   * NumberArrayParam
     *   * DecimalParam
     *   * BooleanParam
     *   * DateTimeParam
     *
     * @return CriteriaParam[]
     */
    public function params(): array;

    public function addCondition(Selection $selection): Selection;

    /**
     * label returns human-friendly and descriptive label of the whole Criteria
     *
     * @return string
     */
    public function label(): string;
}
