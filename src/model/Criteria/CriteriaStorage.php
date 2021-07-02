<?php

namespace Crm\ApplicationModule\Criteria;

class CriteriaStorage
{
    private $criteria = [];

    private $fields = [];

    private $defaultFields = [];

    private $primiaryFields = [];

    public function register(string $table, string $key, CriteriaInterface $criteria): void
    {
        if (!isset($this->criteria[$table])) {
            $this->criteria[$table] = [];
        }
        $this->criteria[$table][$key] = $criteria;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function getTableCriteria(string $table): array
    {
        if (!isset($this->criteria[$table])) {
            return [];
        }
        return $this->criteria[$table];
    }

    public function setFields(string $table, array $fields): void
    {
        $this->fields[$table] = $fields;
    }

    public function setDefaultFields(string $table, array $fields): void
    {
        $this->defaultFields[$table] = $fields;
    }

    public function getTableFields(string $table): array
    {
        if (!isset($this->fields[$table])) {
            return [];
        }
        return $this->fields[$table];
    }

    public function getDefaultTableFields(string $table): array
    {
        if (!isset($this->defaultFields[$table])) {
            return [];
        }
        return $this->defaultFields[$table];
    }

    public function setPrimaryField(string $table, string $field): void
    {
        $this->primiaryFields[$table] = $field;
    }

    public function getPrimaryField(string $table): string
    {
        if (!empty($this->primiaryFields[$table])) {
            return $this->primiaryFields[$table];
        }

        if (!empty($this->defaultFields[$table])) {
            return current($this->defaultFields[$table]);
        }

        return 'id';
    }
}
