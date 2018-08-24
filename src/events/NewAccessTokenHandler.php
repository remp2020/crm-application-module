<?php

namespace Crm\ApplicationModule\Events;

use Crm\ApplicationModule\User\UserData;
use League\Event\AbstractListener;
use League\Event\EventInterface;

// TODO: [users_module] presunut do ContentModule (prip. UsersModule)
class NewAccessTokenHandler extends AbstractListener
{
    private $userData;

    public function __construct(UserData $userData)
    {
        $this->userData = $userData;
    }

    public function handle(EventInterface $event)
    {
        $userId = $event->getUserId();
//        $token = $event->getToken();
        $this->userData->refreshUserTokens($userId);
    }
}
