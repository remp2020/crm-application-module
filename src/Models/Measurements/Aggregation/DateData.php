<?php

namespace Crm\ApplicationModule\Models\Measurements\Aggregation;

use DateTime;
use Nette\Database\Row;

class DateData
{
    private ?int $year;

    private ?int $month;

    private ?int $day;

    private ?int $week;

    public function __construct(?int $year, ?int $month, ?int $day, ?int $week)
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->week = $week;
    }

    public static function fromRow(Row $row): DateData
    {
        return new DateData(
            $row->year ?? null,
            $row->month ?? null,
            $row->day ?? null,
            $row->week ?? null,
        );
    }

    public function year(): ?int
    {
        return $this->year;
    }

    public function month(): ?int
    {
        return $this->month;
    }

    public function day(): ?int
    {
        return $this->day;
    }

    public function week(): ?int
    {
        return $this->week;
    }

    public function getArray(): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'week' => $this->week,
            'sorting_day' => $this->getDateTime(),
        ];
    }

    public function getDateTime(): DateTime
    {
        // this feels very hacky, but unStore seems to be the right method to use

        if ($this->week) {
            $aggregation = new Week();
        } elseif ($this->day) {
            $aggregation = new Day();
        } elseif ($this->month) {
            $aggregation = new Month();
        } else {
            $aggregation = new Year();
        }

        return $aggregation->unStore($this);
    }
}
