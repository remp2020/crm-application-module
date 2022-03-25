<?php

namespace Crm\ApplicationModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApplicationModule\Event\EventsStorage;
use Nette\Http\Response;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class EventsListApiHandler extends ApiHandler
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
        $events = $this->eventsStorage->getEventsPublic();
        $result = [];
        foreach ($events as $event) {
            $result[] = [
                'code' => $event['code'],
                'name' => $event['name'],
            ];
        }

//        $response = new JsonApiResponse(Response::S200_OK, ['status' => 'ok', 'events' => $result]);
        $response = new JsonApiResponse(Response::S200_OK, ['status' => 'ok', 'events' => $result]);

        return $response;
    }
}
