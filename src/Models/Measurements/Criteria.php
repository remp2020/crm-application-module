<?php

namespace Crm\ApplicationModule\Models\Measurements;

use Crm\ApplicationModule\Models\Measurements\Aggregation\Aggregation;
use Crm\ApplicationModule\Models\NowTrait;
use DateTime;

class Criteria
{
    use NowTrait;

    private Aggregation $aggregation;

    private DateTime $epoch;

    private DateTime $from;

    private DateTime $to;

    public function __construct(Aggregation $aggregation, DateTime $epoch, DateTime $from, DateTime $to)
    {
        $this->aggregation = $aggregation;
        $this->epoch = (clone $epoch)->setTime(0, 0);

        if ($from <= $this->epoch) {
            $from = $this->epoch;
        }
        $this->from = (clone $from)->setTime(0, 0);

        if ($to > $this->getNow()) {
            $to = $this->getNow();
        }
        $this->to = (clone $to)->setTime(23, 59, 59);
    }

    public function getAggregation(): Aggregation
    {
        return $this->aggregation;
    }

    public function getFrom(): DateTime
    {
        return $this->from;
    }

    public function getTo(): DateTime
    {
        return $this->to;
    }

    public function getEmptySeries(): Series
    {
        $series = new Series();

        // If $this->from matches the nextDate, make sure it's not skipped by moving it one second behind.
        $dateFrom = $this->aggregation->nextDate((clone $this->from)->modify('-1 second'));

        // If $this->to matches last day of period, this period should be considered complete. We force this by letting
        // system it's working with the following period, by moving it 1 second forward (we already are at 23:59:59).
        $dateTo = (clone $this->to)->add(new \DateInterval('PT1S'));

        if ($this->from == $this->epoch) {
            // We can allow incomplete point if it's the partial period between epoch - first interval.
            $series->addPoint(new Point($this->aggregation, 0, clone $this->from));
        }

        while (($next = $this->aggregation->nextDate($dateFrom)) <= $dateTo) {
            $series->addPoint(new Point($this->aggregation, 0, clone $dateFrom));
            $dateFrom = $next;
        }

        if ($this->to >= $this->getNow()) {
            // We can also allow incomplete point if it's the partial period because of "now" - the period is
            // currently ongoing.
            $series->addPoint(new Point($this->aggregation, 0, clone $dateFrom));
        }

        return $series;
    }
}
