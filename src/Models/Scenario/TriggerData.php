<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Scenario;

class TriggerData
{
    public function __construct(
        public readonly int $userId,
        public readonly array $payload
    ) {
    }
}
