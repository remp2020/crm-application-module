<?php

namespace Crm\ApplicationModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApplicationModule\Event\EventsStorage;
use Nette\Http\Response;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class EventGeneratorsListApiHandler extends ApiHandler
{
    private $eventsStorage;

    public function __construct(
        EventsStorage $eventsStorage
    ) {
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

        $response = new JsonApiResponse(Response::S200_OK, ['status' => 'ok', 'events' => $result]);

        return $response;
    }
}
