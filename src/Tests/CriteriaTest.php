<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Measurements\Aggregation\Month;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Year;
use Crm\ApplicationModule\Models\Measurements\Criteria;
use Nette\Utils\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CriteriaTest extends TestCase
{
    public static function emptySeriesDataProvider()
    {
        // All tests are expecting EPOCH to be '1982-06-01 02:34:56' and NOW to be '1986-04-26 01:23:45'.
        return [
            'fullYears' => [
                'aggregation' => new Year(),
                'from' => '1982-03-01',
                'to' => '1986-04-26 01:23:45',
                'expectedResult' => [
                    1982,
                    1983,
                    1984,
                    1985,
                    1986,
                ],
            ],
            'fullYears_exact' => [
                'aggregation' => new Year(),
                'from' => '1983-01-01',
                'to' => '1985-12-31',
                'expectedResult' => [
                    1983,
                    1984,
                    1985,
                ],
            ],
            'partialYears_skipFirstAndLast' => [
                'aggregation' => new Year(),
                'from' => '1982-11-01', // skipped because it's after epoch
                'to' => '1986-03-01', // skipped because it's before now
                'expectedResult' => [
                    1983,
                    1984,
                    1985,
                ],
            ],
            'partialYears_includeFirst' => [
                'aggregation' => new Year(),
                'from' => '1982-02-01', // included, because it covers full epoch -> 1.period
                'to' => '1984-06-01', // not included, because it's not full 1984
                'expectedResult' => [
                    1982,
                    1983,
                ],
            ],
            'partialYears_includeLast' => [
                'aggregation' => new Year(),
                'from' => '1984-06-01', // not included, because it's not full 1984
                'to' => '1986-06-01', // included, because it's currently period, partial data are fine
                'expectedResult' => [
                    1985,
                    1986,
                ],
            ],

            'fullMonths' => [
                'aggregation' => new Month(),
                'from' => '1982-03-01',
                'to' => '1982-11-30',
                'expectedResult' => [
                    '1982-06', // we start with June, because it's the epoch
                    '1982-07',
                    '1982-08',
                    '1982-09',
                    '1982-10',
                    '1982-11', // this month is included, because "to" covers full month, otherwise it wouldn't be
                ],
            ],
            'fullMonths_exact' => [
                'aggregation' => new Month(),
                'from' => '1983-01-01',
                'to' => '1983-04-30',
                'expectedResult' => [
                    '1983-01',
                    '1983-02',
                    '1983-03',
                    '1983-04',
                ],
            ],
            'partialMonths_skipFirst' => [
                'aggregation' => new Month(),
                'from' => '1982-06-10', // skipped because it's after epoch
                'to' => '1982-09-30', // skipped because it's before now
                'expectedResult' => [
                    '1982-07',
                    '1982-08',
                    '1982-09',
                ],
            ],
            'partialMonths_includeFirst' => [
                'aggregation' => new Month(),
                'from' => '1982-06-01', // included, because it covers full epoch -> 1.period
                'to' => '1982-08-04', // not included, because it's not full 1984
                'expectedResult' => [
                    '1982-06',
                    '1982-07',
                ],
            ],
            'partialMonths_includeLast' => [
                'aggregation' => new Month(),
                'from' => '1986-02-02', // not included, because it's not full 1984
                'to' => '1986-04-28', // included, because it's currently period, partial data are fine
                'expectedResult' => [
                    '1986-03',
                    '1986-04',
                ],
            ],
        ];
    }

    #[DataProvider('emptySeriesDataProvider')]
    public function testEmptySeries($aggregation, $from, $to, $expectedResult): void
    {
        $criteria = new Criteria(
            $aggregation,
            DateTime::from('1982-06-01'),
            DateTime::from($from),
            DateTime::from($to),
        );
        $criteria->setNow(DateTime::from('1986-04-26 01:23:45'));
        $emptySeries = $criteria->getEmptySeries();
        $this->assertEquals(
            $expectedResult,
            array_keys($emptySeries->points()),
        );
    }
}
