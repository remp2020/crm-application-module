<?php

namespace Crm\ApplicationModule\Models\DataProvider;

enum AuditLogHistoryItemChangeIndicatorEnum: string
{
    case Default = 'default';
    case Primary = 'primary';
    case Success = 'success';
    case Info = 'info';
    case Warning = 'warning';
    case Danger = 'danger';
}
