<?php

namespace Crm\ApplicationModule\Models\Access;

use Nette\Security\User;

class AccessManager
{
    /** @var ProviderInterface[] */
    private $providers = [];

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function addAccessProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function hasAccess($userId, $access): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->available($access)) {
                return $provider->hasAccess($userId, $access);
            }
        }
        throw new UnknownAccessException($access, "Unknown access '{$access}' (userid: {$userId})");
    }

    public function access($access): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }
        return $this->hasAccess($this->user->getId(), $access);
    }
}
