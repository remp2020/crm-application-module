<?php

namespace Crm\ApplicationModule\Criteria;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

interface ScenariosCriteriaInterface
{
    /**
     * params returns an array of CriteriaParam definitions available for the Criteria.
     *
     * The generator and UI currently support following type of parameters:
     *
     *   * StringLabeledArrayParam
     *   * BooleanParam
     *
     * @return CriteriaParam[]
     */
    public function params(): array;

    /**
     * Adds condition to $selection query according to $values parameter.
     * Returns false if criteria based on $criterionItemRow is evaluated as untrue otherwise returns true.
     *
     * @param Selection $selection
     * @param $values - is object containing data returned by user interface settings (from Scenario Builder)
     *  of assigned CriteriaParam (one you return in params() method). For example, BooleanParam may return object
     *  {"selection": true}, specifying that condition parameter should be evaluated as true.
     *  One should reflect such settings in condition added to $selection query.
     * @param IRow $criterionItemRow - contains IRow object on which $selection query is going to be built.
     *  This depends on which event you have registered the criteria (see CrmModule#registerScenariosCriteria()).
     *  For example, if the criteria is registered on subscription event, the criteria will receive the subscription
     *  object that has triggered the particular scenario.
     *
     * @return bool
     */
    public function addCondition(Selection $selection, $values, IRow $criterionItemRow): bool;

    /**
     * label returns human-friendly and descriptive label of the whole Criteria
     *
     * @return string
     */
    public function label(): string;
}
