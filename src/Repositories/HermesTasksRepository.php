<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Hermes\LogRedact;
use Crm\ApplicationModule\Models\Config\HermesConfig;
use Crm\ApplicationModule\Models\Database\Repository;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tomaj\Hermes\MessageInterface;

class HermesTasksRepository extends Repository
{
    const STATE_DONE  = 'done';
    const STATE_ERROR = 'error';

    protected $tableName = 'hermes_tasks';


    public function __construct(
        Explorer $database,
        private readonly HermesConfig $hermesConfig,
    ) {
        parent::__construct($database);
    }

    final public function add(MessageInterface $message, string $state)
    {
        $createdAt = DateTime::createFromFormat('U.u', sprintf('%.4f', $message->getCreated()));
        $executeAt = $message->getExecuteAt() ?
            DateTime::createFromFormat('U.u', sprintf('%.4f', $message->getExecuteAt())) :
            null;

        $payload = $message->getPayload();
        $redactedFields = $this->hermesConfig->getRedactedFields();
        if (!empty($redactedFields)) {
            $payload = LogRedact::redactArray($payload, $redactedFields);
        }

        return $this->insert([
            'message_id' => $message->getId(),
            'type' => $message->getType(),
            'payload' => Json::encode($payload),
            'retry' => $message->getRetries(),
            'state' => $state,
            'created_at' => $createdAt,
            'execute_at' => $executeAt,
            'processed_at' => new DateTime(),
        ]);
    }

    final public function getStateCounts(\DateTime $processedFrom, array $states = [])
    {
        $query = $this->getTable()
            ->select('state, type, count(*) AS count')
            ->where('processed_at >= ?', $processedFrom)
            ->group('state, type')
            ->order('count DESC');

        if (!empty($states)) {
            $query->where(['state' => $states]);
        }

        return $query;
    }

    public function getErrorTasks()
    {
        return $this->getTable()->where(['state' => self::STATE_ERROR])->order('created_at DESC');
    }
}
