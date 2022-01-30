<?php

namespace Crm\ApplicationModule\Authenticator;

interface AuthenticatorManagerInterface
{
    public function registerAuthenticator(AuthenticatorInterface $authenticator, $priority = 100);

    public function getAuthenticators();
}
