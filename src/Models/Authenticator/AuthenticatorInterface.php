<?php

namespace Crm\ApplicationModule\Authenticator;

use Nette\Database\Table\ActiveRow;

interface AuthenticatorInterface
{
    /**
     * Authenticates & returns user if successful.
     *
     * @return bool|mixed|ActiveRow
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
