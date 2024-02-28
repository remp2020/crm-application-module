<?php

namespace Crm\ApplicationModule\Models\Database;

/**
 * This interface defines methods for object/database row
 * which can provide its original data after it was updated.
 *
 * @package Crm\ApplicationModule\Models\Database
 */
interface OriginalDataAwareInterface
{
    public function setOriginalData(array $values): void;

    public function getOriginalData(): array;
}
