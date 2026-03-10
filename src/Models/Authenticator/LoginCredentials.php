<?php

namespace Crm\ApplicationModule\Models\Authenticator;

use Nette\Security\IIdentity;

/**
 * Pre-authentication identity that carries credentials through Nette\Security\User::login().
 *
 * In Nette Security 3.2+, login() only accepts IIdentity. By wrapping credential arrays
 * in this class, we pass them through to our custom authenticator chain unchanged.
 */
class LoginCredentials implements IIdentity
{
    public function __construct(
        private readonly array $credentials,
    ) {
    }

    public function getCredentials(): array
    {
        return $this->credentials;
    }

    public function getId(): mixed
    {
        return null;
    }

    public function getRoles(): array
    {
        return [];
    }
}
