<?php

namespace Crm\ApplicationModule\Criteria;

class ScenariosCriteriaStorage
{
    private $criteria = [];

    public function register(string $event, string $key, ScenariosCriteriaInterface $criteria): void
    {
        if (!isset($this->criteria[$event])) {
            $this->criteria[$event] = [];
        }
        $this->criteria[$event][$key] = $criteria;
    }

    /**
     * @return ScenariosCriteriaInterface[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function getEventCriterion(string $event, $key): ScenariosCriteriaInterface
    {
        return $this->criteria[$event][$key];
    }
}
