<?php

namespace Crm\ApplicationModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tomaj\Hermes\MessageInterface;

class HermesTasksRepository extends Repository
{
    const STATE_DONE  = 'done';
    const STATE_ERROR = 'error';

    protected $tableName = 'hermes_tasks';

    final public function add(MessageInterface $message, $state)
    {
        $createdAt = DateTime::from($message->getCreated());
        $executeAt = $message->getExecuteAt() ? DateTime::from($message->getExecuteAt()) : null;

        return $this->insert([
            'message_id' => $message->getId(),
            'type' => $message->getType(),
            'payload' => Json::encode($message->getPayload()),
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
