<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Domain;

use DomainException;
use Nette\Utils\DateTime;

class Date
{
    public function __construct(public readonly string $value)
    {
        $valueParts = [];
        $matchResult = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $valueParts);
        if ($matchResult === 0) {
            throw new DomainException('Field contains an invalid date format (YYYY-MM-DD).');
        }

        [, $year, $month, $day] = $valueParts;
        if (checkdate((int) $month, (int) $day, (int) $year) === false) {
            throw new DomainException('Field contains an invalid date.');
        }
    }

    public function toNativeDateTime(): DateTime
    {
        return DateTime::createFromFormat('Y-m-d', $this->value);
    }
}
