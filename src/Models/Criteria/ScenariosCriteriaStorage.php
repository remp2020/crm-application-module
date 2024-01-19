<?php

namespace Crm\ApplicationModule\Models\Criteria;

class ScenariosCriteriaStorage
{
    private $criteria = [];

    private $conditionModels = [];

    public function register(string $event, string $key, ScenariosCriteriaInterface $criteria): void
    {
        if (!isset($this->criteria[$event])) {
            $this->criteria[$event] = [];
        }
        $this->criteria[$event][$key] = $criteria;
    }

    public function registerConditionModel(string $event, ScenarioConditionModelInterface $conditionModel)
    {
        if (isset($this->conditionModels[$event])) {
            throw new \Exception("Condition model for event '{$event}' already registered");
        }
        $this->conditionModels[$event] = $conditionModel;
    }

    /**
     * @return ScenariosCriteriaInterface[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * @param string $event
     * @return ScenarioConditionModelInterface|null
     */
    public function getConditionModel(string $event): ?ScenarioConditionModelInterface
    {
        return $this->conditionModels[$event] ?? null;
    }

    public function getEventCriterion(string $event, $key): ScenariosCriteriaInterface
    {
        return $this->criteria[$event][$key];
    }
}
