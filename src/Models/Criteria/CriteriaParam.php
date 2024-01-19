<?php

namespace Crm\ApplicationModule\Models\Criteria;

interface CriteriaParam
{
    public function key(): string;

    public function label(): string;

    public function blueprint(): array;

    public function type(): string;
}
