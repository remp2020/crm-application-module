<?php

namespace Crm\ApplicationModule\Models\Measurements;

use Nette\Database\Explorer;

abstract class BaseMeasurement
{
    /**
     * GROUPINGS in the child classes configures separate groups the measurement should be calculated for.
     * Value "null" indicates no grouping which is the default for all measurements.
     * If you want the measurement to be calculated based on a more specific split, measurement should specify it here.
     */
    protected const GROUPS = [];

    /**
     * CODE identifies the measurement implementation. Measurement's code needs to be referenced when you need to fetch
     * the generated data series.
     */
    public const CODE = 'unknown';

    protected $db;

    /**
     * Generates the series of points to display.
     *
     *  - Use $criteria->getEmptySeries() to generate base series object.
     *
     *  - There are two approaches how to calculate the series:
     *
     *    1. Calculate the measurement for each term separately. You can sue following skeleton to start:
     *
     *       $date = clone $criteria->getFrom();
     *       while ($date <= $criteria->getTo()) {
     *          $next = $criteria->getAggregation()->nextDate($date);
     *
     *          $query = "SELECT COUNT(*) AS count FROM foo WHERE ?";
     *          $rows = $this->db()->query($query, [
     *              'start_time <=' => $next,
     *              'end_time >=' => $date,
     *          ]);
     *
     *          foreach ($rows as $row) {
     *              $point = new Point($criteria->getAggregation(), $row->count, clone $date);
     *              $series->setPoint($point);
     *          }
     *          $date = $next;
     *       }
     *
     *    2. Calculate all measurements with the single query. Use following skeleton to start:
     *
     *       $series = $criteria->getEmptySeries();
     *
     *       foreach ($this->groups() as $group) {
     *          $fields = $criteria->getAggregation()->select('users.created_at');
     *          if ($group) {
     *              $fields[] = $group;
     *          }
     *          $fieldsString = implode(',', $fields);
     *
     *          $query = "SELECT {$fieldsString}, COUNT(*) AS count FROM foo WHERE ?
     *                    GROUP BY {$criteria->getAggregation()->group($fields)}
     *                    ORDER BY {$criteria->getAggregation()->group($fields)}";
     *
     *          $result = $this->db()->query($query, [
     *              'created_at >=' => $criteria->getFrom(),
     *              'created_at <' => $criteria->getTo()
     *          ]);
     *
     *          $rows = $result->fetchAll();
     *          foreach ($rows as $row) {
     *              $point = new Point(
     *                  $criteria->getAggregation(),
     *                  $row->count,
     *                  DateData::fromRow($row)->getDateTime(),
     *                  $group ? $row->{$group} : null
     *              );
     *              if ($group) {
     *                  $series->setGroupPoint($group, $point);
     *              } else {
     *                  $series->setPoint($point);
     *              }
     *          }
     *      }
     *
     */
    abstract public function calculate(Criteria $criteria): Series;

    public function code(): string
    {
        return static::CODE;
    }

    public function groups(): array
    {
        return array_merge([null], static::GROUPS);
    }

    public function setDatabase(Explorer $db): void
    {
        $this->db = $db;
    }

    protected function db(): Explorer
    {
        return $this->db;
    }
}
