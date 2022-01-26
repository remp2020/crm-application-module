<?php

namespace Crm\ApplicationModule\Criteria;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

interface ScenariosCriteriaInterface
{
    /**
     * Criteria may define several CriteriaParams.
     * Each param is rendered as an input component in ScenarioBuilder (grouped under the criteria)
     *
     * @return CriteriaParam[]
     */
    public function params(): array;

    /**
     * Adds conditions to $selection query according to $paramValues criteria parameters saved values (saved by user in ScenarioBuilder.
     * Returns false if criteria based on $criterionItemRow is evaluated as untrue, otherwise returns true.
     *
     * @param Selection $selection
     * @param array     $paramValues array containing values saved by CriteriaParams registered in params() method.
     * Values in array are keyed by CriteriaParams' own keys.
     *
     * Example:
     * If a BooleanParam("has_id", "ID") is registered in params() method,
     * $paramValues may contain array: ["has_id": {"selection": true}]
     * 'selection' value represents state of boolean parameter in ScenarioBuilder (toggled on/off)
     *
     * @param IRow $criterionItemRow contains IRow object on which $selection query is going to be built.
     * This depends on event you have registered the criteria (see CrmModule#registerScenariosCriteria()).
     * For example, if the criteria is registered on subscription event,
     * the criteria will receive the subscription object that has triggered the particular scenario.
     *
     * @return bool
     */
    public function addConditions(Selection $selection, array $paramValues, IRow $criterionItemRow): bool;

    /**
     * label returns human-friendly and descriptive label of the whole Criteria
     *
     * @return string
     */
    public function label(): string;
}
