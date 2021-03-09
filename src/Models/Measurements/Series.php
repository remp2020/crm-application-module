<?php

namespace Crm\ApplicationModule\Models\Measurements;

final class Series
{
    /** @var array<PointAggregate>  */
    private array $points = [];

    public function setPoint(Point $point): self
    {
        if (isset($this->points[$point->key()])) {
            $this->points[$point->key()] = new PointAggregate($point);
        }
        return $this;
    }

    public function setGroupPoint(string $grouping, Point $point): self
    {
        if (isset($this->points[$point->key()])) {
            $this->points[$point->key()]->addGroupPoint($grouping, $point);
        }
        return $this;
    }

    public function addPoint(Point $point): self
    {
        $this->points[$point->key()] = new PointAggregate($point);
        return $this;
    }

    public function addGroupPoint(string $grouping, Point $point): self
    {
        $this->points[$point->key()]->addGroupPoint($grouping, $point);
        return $this;
    }

    public function points(): array
    {
        return $this->points;
    }
}
