<?php

namespace Crm\ApplicationModule\Criteria;

class CriteriaStorage
{
    private $criteria = [];

    public function register($table, $key, CriteriaInterface $criteria)
    {
        if (!isset($this->criteria[$table])) {
            $this->criteria[$table] = [];
        }
        $this->criteria[$table][$key] = $criteria;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function getTableCriteria($table)
    {
        if (!isset($this->criteria[$table])) {
            return [];
        }
        return $this->criteria[$table];
    }
}
