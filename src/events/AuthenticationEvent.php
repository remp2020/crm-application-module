<?php

namespace Crm\ApplicationModule\Events;

use League\Event\AbstractEvent;
use Nette\Http\IRequest;

class AuthenticationEvent extends AbstractEvent
{
    private $request;

    private $userId;

    public function __construct($request, $userId)
    {
        $this->request = $request;
        $this->userId = $userId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRequest(): IRequest
    {
        return $this->request;
    }
}
