<?php

namespace Crm\ApplicationModule\Models\Measurements;

class PointAggregate
{
    private Point $point;

    private array $groups;

    public function __construct(Point $point, array $groupings = [])
    {
        $this->point = $point;
        $this->groups = $groupings;
    }

    public function point(): Point
    {
        return $this->point;
    }

    public function addGroupPoint(string $group, Point $point)
    {
        if (!isset($this->groups[$group])) {
            $this->groups[$group] = [];
        }
        $this->groups[$group][] = $point;
    }

    public function getGroups(): array
    {
        return array_keys($this->groups);
    }

    /**
     * @param string $grouping
     * @return array<Point>
     */
    public function groupPoints(string $grouping): array
    {
        if (!isset($this->groups[$grouping])) {
            // not sure if this is ok :-)
            return [];
        }
        return $this->groups[$grouping];
    }
}
