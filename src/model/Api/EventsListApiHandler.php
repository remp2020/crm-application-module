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
        $events = $this->eventsStorage->getEvents();
        $result = [];
        foreach ($events as $code => $event) {
            $result[$code] = [
                'code' => $code,
                'title' => ucfirst(str_replace('_', ' ', $code)),
            ];
        }

        $response = new JsonResponse(['status' => 'ok', 'events' => $result]);
        $response->setHttpCode(Response::S200_OK);

        return $response;
    }
}
