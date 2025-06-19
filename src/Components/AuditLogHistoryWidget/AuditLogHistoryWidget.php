<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Components\AuditLogHistoryWidget;

use Crm\ApplicationModule\Models\DataProvider\AuditLogHistoryDataProviderInterface;
use Crm\ApplicationModule\Models\DataProvider\AuditLogHistoryDataProviderItem;
use Crm\ApplicationModule\Models\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Models\Widget\BaseLazyWidget;
use Crm\ApplicationModule\Models\Widget\LazyWidgetManager;
use Nette\Database\Table\ActiveRow;

class AuditLogHistoryWidget extends BaseLazyWidget
{
    private string $templateName = 'audit_log_history_widget.latte';

    public function __construct(
        LazyWidgetManager $lazyWidgetManager,
        private readonly DataProviderManager $dataProviderManager,
    ) {
        parent::__construct($lazyWidgetManager);
    }

    public function render(ActiveRow $entity): void
    {
        $tableName = $entity->getTable()->getName();
        $signature = $entity->getSignature();

        /** @var AuditLogHistoryDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders(
            'admin.dataprovider.audit_log_history_widget',
            AuditLogHistoryDataProviderInterface::class,
        );
        /** @var AuditLogHistoryDataProviderItem[] $history */
        $history = [];
        foreach ($providers as $provider) {
            $history += $provider->provide($tableName, $signature);
        }

        if (empty($history)) {
            return;
        }

        usort(
            $history,
            fn($a, $b) => $b->getDateTime() <=> $a->getDateTime(),
        );

        $this->template->tableName = $tableName;
        $this->template->signature = $signature;
        $this->template->history = $history;
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
