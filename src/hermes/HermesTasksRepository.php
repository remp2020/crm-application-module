<?php

namespace Crm\ApplicationModule\Repository;

use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\ApplicationModule\Repository;
use Nette\Utils\DateTime;
use Nette\Utils\Json;

class HermesTasksRepository extends Repository
{
    const STATE_DONE  = 'done';
    const STATE_ERROR = 'error';

    protected $tableName = 'hermes_tasks';

    public function add(HermesMessage $message, $state)
    {
        $createdAt = DateTime::from($message->getCreated());

        return $this->insert([
            'id' => $message->getId(),
            'type' => $message->getType(),
            'payload' => Json::encode($message->getPayload()),
            'state' => $state,
            'created_at' => $createdAt,
            'processed_at' => new DateTime(),
        ]);
    }

    public function getStateCounts()
    {
        return $this->getTable()->group('state, type')->select('state, type, count(*) AS count')->order('count DESC');
    }
}
