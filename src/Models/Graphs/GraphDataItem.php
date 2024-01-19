<?php

namespace Crm\ApplicationModule\Models\Graphs;

use Crm\ApplicationModule\Graphs\Scale\ScaleInterface;

class GraphDataItem
{
    const SCALE_DAYS = 'days';
    const SCALE_WEEKS = 'weeks';
    const SCALE_MONTHS = 'months';

    public string $name = '';
    public string $tag = '';

    private ScaleInterface $scale;
    private Criteria $criteria;
    private string $scaleProvider = 'mysql';

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;
        return $this;
    }

    public function setScaleProvider(string $provider): self
    {
        $this->scaleProvider = $provider;
        return $this;
    }

    public function getScaleProvider(): string
    {
        return $this->scaleProvider;
    }

    public function setScale(ScaleInterface $scale): self
    {
        $this->scale = $scale;
        return $this;
    }

    public function getData(): array
    {
        $zeroKeys = $this->scale->getKeys($this->criteria->getStart(), $this->criteria->getEnd());

        $dbData = $this->scale->getDatabaseData($this->criteria, $this->tag);

        return $this->formatData($zeroKeys, $dbData);
    }

    public function getSeriesData(): array
    {
        $zeroKeys = $this->scale->getKeys($this->criteria->getStart(), $this->criteria->getEnd());

        if (count($this->criteria->rangeFields) > 0) {
            $dbData = $this->scale->getDatabaseRangeData($this->criteria);
        } else {
            $dbData = $this->scale->getDatabaseSeriesData($this->criteria);
        }

        $db = [];
        if (empty($dbData)) {
            $db[$this->name] = $this->formatData($zeroKeys, []);
        }
        foreach ($dbData as $k => $v) {
            $db[$this->name . $k] = $this->formatData($zeroKeys, $v);
        }
        return $db;
    }

    private function formatData(array $zeroValues, array $dbData): array
    {
        $result = [];
        foreach ($zeroValues as $key => $date) {
            if (isset($dbData[$key])) {
                $result[$date] = $dbData[$key];
            } else {
                $result[$date] = 0;
            }
        }
        return $result;
    }
}
