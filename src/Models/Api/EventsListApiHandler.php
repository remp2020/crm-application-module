<?php

namespace Crm\ApplicationModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Response\ApiResponseInterface;
use Crm\ApplicationModule\Event\EventsStorage;
use Nette\Http\Response;

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

    public function handle(array $params): ApiResponseInterface
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
        $response = new JsonResponse(['status' => 'ok', 'events' => $result]);
        $response->setHttpCode(Response::S200_OK);

        return $response;
    }
}
