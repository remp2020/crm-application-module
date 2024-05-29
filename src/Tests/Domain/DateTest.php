<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Tests\Domain;

use Crm\ApplicationModule\Domain\Date;
use DomainException;
use Nette\Utils\DateTime;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
{
    public function testValidDate(): void
    {
        $date = new Date('2021-01-01');
        $this->assertSame('2021-01-01', $date->value);
    }

    public function testInvalidDateFormat(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Field contains an invalid date format (YYYY-MM-DD).');

        new Date('2021-01-32-');
    }

    public function testInvalidDate(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Field contains an invalid date.');

        new Date('2021-01-32');
    }

    public function testConvertToNativeDateTime(): void
    {
        $expectedDate = DateTime::createFromFormat('Y-m-d', '2021-01-01');

        $date = new Date('2021-01-01');
        $this->assertEquals($expectedDate, $date->toNativeDateTime());
    }
}
