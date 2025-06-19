<?php

namespace Crm\ApplicationModule\Models\DataProvider;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class AuditLogHistoryDataProviderItem
{
    public function __construct(
        readonly private DateTime $dateTime,
        readonly private string $operation,
        readonly private ?ActiveRow $user = null,
        private ?AuditLogHistoryItemChangeIndicatorEnum $changeIndicator = null,
        private ?array $messages = null,
    ) {
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getUser(): ?ActiveRow
    {
        return $this->user;
    }

    public function getChangeIndicator(): ?AuditLogHistoryItemChangeIndicatorEnum
    {
        return $this->changeIndicator;
    }

    public function setChangeIndicator(AuditLogHistoryItemChangeIndicatorEnum $changeIndicator): void
    {
        $this->changeIndicator = $changeIndicator;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    /**
     * Adds a message to the audit log history item.
     *
     * @param string $message Nette localization key
     * @param array|null $params Optional parameters for the message
     */
    public function addMessage(string $message, ?array $params = null): void
    {
        $this->messages[] = [
            'message' => $message,
            'params' => $params,
        ];
    }
}
