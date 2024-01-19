<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Models\Database\DateFieldsProcessorTrait;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

class DateFieldsProcessorTraitTest extends TestCase
{
    private $dateFieldsProcessor;

    protected function setUp(): void
    {
        $this->dateFieldsProcessor = $this->getMockForTrait(DateFieldsProcessorTrait::class);
    }

    public function testDateRFC3339(): void
    {
        $inputDate = new DateTime('2020-01-10 8:00');
        $fields = [(clone $inputDate)];

        $result = $this->dateFieldsProcessor->processDateFields($fields);
        $this->assertCount(1, $result);

        $resultDate = $result[0];
        $expected = clone $inputDate;
        $dateFormat = 'Y-m-d H:i:s';
        $this->assertEquals($expected->format($dateFormat), $resultDate->format($dateFormat));
    }

    public function testDateRFC3339FromUTC(): void
    {
        $inputDate = new DateTime('2020-01-10T08:00:00.000Z');
        $fields = [(clone $inputDate)];

        $result = $this->dateFieldsProcessor->processDateFields($fields);

        $this->assertCount(1, $result);

        $resultDate = $result[0];
        $expected = (clone $inputDate)->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $dateFormat = 'Y-m-d H:i:s';
        $this->assertEquals($expected->format($dateFormat), $resultDate->format($dateFormat));
    }

    public function testMulipleValues(): void
    {
        $inputDate_1 = new DateTime('2020-01-10T08:00:00.000Z');
        $inputDate_2 = new DateTime('2020-01-10 8:00');
        $inputString = 'string';
        $inputNumber = 10;

        $fields = [(clone $inputDate_1), (clone $inputDate_2), $inputString, $inputNumber];

        $result = $this->dateFieldsProcessor->processDateFields($fields);

        $this->assertCount(4, $result);
        $dateFormat = 'Y-m-d H:i:s';

        $resultDate_1 = $result[0];
        $expected = (clone $inputDate_1)->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $this->assertEquals($expected->format($dateFormat), $resultDate_1->format($dateFormat));

        $resultDate_2 = $result[1];
        $expected = clone $inputDate_2;
        $this->assertEquals($expected->format($dateFormat), $resultDate_2->format($dateFormat));

        $resultString = $result[2];
        $this->assertEquals($inputString, $resultString);

        $resultNumber = $result[3];
        $this->assertEquals($inputNumber, $resultNumber);
    }
}
