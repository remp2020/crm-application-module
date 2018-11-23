<?php

namespace Crm\ApplicationModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApplicationModule\Event\EventsStorage;
use Nette\Http\Response;

class EventsListApiHandler extends ApiHandler
{
    private $eventsStorage;

    public function __construct(
        EventsStorage $eventsStorage
    ) {
        $this->eventsStorage = $eventsStorage;
    }

    public function params()
    {
        return [];
    }

    public function handle(ApiAuthorizationInterface $authorization)
    {
        $events = $this->eventsStorage->getEventsPublic();
        $result = [];
        foreach ($events as $event) {
            $result[] = [
                'code' => $event['code'],
                'name' => $event['name'],
            ];
        }

        $response = new JsonResponse(['status' => 'ok', 'events' => $result]);
        $response->setHttpCode(Response::S200_OK);

        return $response;
    }
}
