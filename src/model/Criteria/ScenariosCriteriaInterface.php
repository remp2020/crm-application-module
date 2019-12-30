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
     *   * StringLabeledArrayParam
     *
     * @return CriteriaParam[]
     */
    public function params(): array;

    public function addCondition(Selection $selection, $values);

    /**
     * label returns human-friendly and descriptive label of the whole Criteria
     *
     * @return string
     */
    public function label(): string;
}
