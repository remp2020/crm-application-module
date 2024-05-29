<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Domain;

use DomainException;
use Nette\Utils\DateTime;

class OptionalDateTimeRange
{
    public function __construct(
        public readonly ?DateTime $dateFrom,
        public readonly ?DateTime $dateTo,
    ) {
        if ($dateFrom === null || $dateTo === null) {
            return;
        }

        if ($dateFrom > $dateTo) {
            throw new DomainException("Date 'from' must be earlier than date 'to'.");
        }
    }
}
