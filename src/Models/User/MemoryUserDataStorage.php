<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Models\User;

use Crm\ApplicationModule\Models\ResettableInterface;

class MemoryUserDataStorage implements UserDataStorageInterface, ResettableInterface
{
    /** @var array<string, string> */
    private array $store = [];

    public function load($token): ?string
    {
        return $this->store[$token] ?? null;
    }

    public function multiLoad(array $tokens): array
    {
        $result = [];
        foreach ($tokens as $token) {
            $result[$token] = $this->store[$token] ?? null;
        }

        return $result;
    }

    public function store($token, $data): void
    {
        $this->store[$token] = (string) $data;
    }

    public function multiStore(array $tokens, $data): bool
    {
        if (!$data || !$tokens) {
            return false;
        }
        foreach ($tokens as $token) {
            $this->store[$token] = (string) $data;
        }
        return true;
    }

    public function remove($token): void
    {
        unset($this->store[$token]);
    }

    public function multiRemove(array $tokens): void
    {
        foreach ($tokens as $token) {
            unset($this->store[$token]);
        }
    }

    public function iterateTokens(callable $callback): void
    {
        foreach ($this->store as $token => $data) {
            $callback($token, $data);
        }
    }

    public function reset(): void
    {
        $this->store = [];
    }
}
