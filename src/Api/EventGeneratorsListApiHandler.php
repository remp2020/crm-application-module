<?php

namespace Crm\ApplicationModule\Api;

use Crm\ApiModule\Models\Api\ApiHandler;
use Crm\ApplicationModule\Models\Event\EventsStorage;
use Nette\Http\IResponse;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class EventGeneratorsListApiHandler extends ApiHandler
{
    private $eventsStorage;

    public function __construct(
        EventsStorage $eventsStorage
    ) {
        parent::__construct();
        $this->eventsStorage = $eventsStorage;
    }

    public function params(): array
    {
        return [];
    }

    public function handle(array $params): ResponseInterface
    {
        $events = $this->eventsStorage->getEvents();
        $eventGenerators = $this->eventsStorage->getEventGenerators();

        $result = [];
        foreach ($eventGenerators as $code => $eventGenerator) {
            $result[] = [
                'code' => $code,
                'name' => $events[$code]['name'],
            ];
        }

        $response = new JsonApiResponse(IResponse::S200_OK, ['status' => 'ok', 'events' => $result]);

        return $response;
    }
}
