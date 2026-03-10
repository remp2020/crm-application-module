<?php

namespace Crm\ApplicationModule\Models\Authenticator;

use Crm\UsersModule\Models\Auth\UserAuthenticator;
use Nette\Security\Authenticator;
use Nette\Security\Authorizator;
use Nette\Security\User;
use Nette\Security\UserStorage;

class CrmUser extends User
{
    public function __construct(
        private readonly UserAuthenticator $userAuthenticator,
        UserStorage $storage,
        ?Authenticator $authenticator = null,
        ?Authorizator $authorizator = null,
    ) {
        parent::__construct($storage, $authenticator, $authorizator);
    }

    // string|IIdentity $user
    public function login(#[\SensitiveParameter] $username, #[\SensitiveParameter] ?string $password = null): void
    {
        if ($username instanceof LoginCredentials) {
            $identity = $this->userAuthenticator->authenticateCredentials($username->getCredentials());
            parent::login($identity);
            return;
        }

        parent::login($username, $password);
    }
}
