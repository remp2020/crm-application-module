<?php

namespace Crm\ApplicationModule\Authenticator;

class AuthenticatorManager implements AuthenticatorManagerInterface
{
    private $authenticators = [];

    public function registerAuthenticator(AuthenticatorInterface $authenticator, $priority = 100)
    {
        if (isset($this->authenticators[$priority])) {
            do {
                $priority++;
            } while (isset($this->authenticators[$priority]));
        }
        $this->authenticators[$priority] = $authenticator;
    }

    public function getAuthenticators()
    {
        krsort($this->authenticators);
        return $this->authenticators;
    }
}
