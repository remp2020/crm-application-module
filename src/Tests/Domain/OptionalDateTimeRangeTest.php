<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Tests\Domain;

use Crm\ApplicationModule\Domain\OptionalDateTimeRange;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

class OptionalDateTimeRangeTest extends TestCase
{
    public function testOptionalDateRange(): void
    {
        $dateFrom = new DateTime('2021-01-01');
        $dateTo = new DateTime('2021-01-02');

        $optionalDateTimeRange = new OptionalDateTimeRange($dateFrom, $dateTo);
        $this->assertSame($dateFrom, $optionalDateTimeRange->dateFrom);
        $this->assertSame($dateTo, $optionalDateTimeRange->dateTo);
    }

    public function testOptionalDateRangeWithNullDateFrom(): void
    {
        $dateTo = new DateTime('2021-01-02');

        $optionalDateTimeRange = new OptionalDateTimeRange(null, $dateTo);
        $this->assertNull($optionalDateTimeRange->dateFrom);
        $this->assertSame($dateTo, $optionalDateTimeRange->dateTo);
    }

    public function testOptionalDateRangeWithNullDateTo(): void
    {
        $dateFrom = new DateTime('2021-01-01');

        $optionalDateTimeRange = new OptionalDateTimeRange($dateFrom, null);
        $this->assertSame($dateFrom, $optionalDateTimeRange->dateFrom);
        $this->assertNull($optionalDateTimeRange->dateTo);
    }

    public function testOptionalDateRangeWithNullDates(): void
    {
        $optionalDateTimeRange = new OptionalDateTimeRange(null, null);

        $this->assertNull($optionalDateTimeRange->dateFrom);
        $this->assertNull($optionalDateTimeRange->dateTo);
    }

    public function testOptionalDateRangeWithDateFromGreaterThanDateTo(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Date 'from' must be earlier than date 'to'.");

        $dateFrom = new DateTime('2021-01-02');
        $dateTo = new DateTime('2021-01-01');
        new OptionalDateTimeRange($dateFrom, $dateTo);
    }
}
