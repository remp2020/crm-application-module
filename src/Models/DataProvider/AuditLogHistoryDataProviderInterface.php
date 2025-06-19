<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\DataProvider;

interface AuditLogHistoryDataProviderInterface extends DataProviderInterface
{
    /**
     * @return AuditLogHistoryDataProviderItem[]
     */
    public function provide(string $tableName, string $signature): array;
}
