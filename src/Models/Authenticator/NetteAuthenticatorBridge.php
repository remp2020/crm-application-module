<?php

namespace Crm\ApplicationModule\Models\Authenticator;

use Crm\UsersModule\Models\Auth\UserAuthenticator;
use Nette\Security\Authenticator;
use Nette\Security\IIdentity;

class NetteAuthenticatorBridge implements Authenticator
{
    public function __construct(
        private UserAuthenticator $userAuthenticator,
    ) {
    }

    public function authenticate(#[\SensitiveParameter] string $user, #[\SensitiveParameter] string $password): IIdentity
    {
        return $this->userAuthenticator->authenticateCredentials([
            'username' => $user,
            'password' => $password,
        ]);
    }
}
