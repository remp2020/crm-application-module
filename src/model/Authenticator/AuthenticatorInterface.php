<?php

namespace Crm\ApplicationModule\Authenticator;

interface AuthenticatorInterface
{
    /**
     * Authenticates & returns user if successful.
     *
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    public function authenticate();

    /**
     * Sets credentials.
     *
     * Elements of array are based on implementation of this interface.
     */
    public function setCredentials(array $credentials) : AuthenticatorInterface;

    /**
     * Returns source of authentication request.
     */
    public function getSource() : string;

    public function shouldRegenerateToken(): bool;
}
