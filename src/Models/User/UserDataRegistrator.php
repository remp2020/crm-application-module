<?php

namespace Crm\ApplicationModule\User;

class UserDataRegistrator
{
    /** @var UserDataProviderInterface[] */
    private $providers = [];

    /** @var array */
    private $protectedData = [];

    private $sorted = false;

    public function addUserDataProvider(UserDataProviderInterface $userDataProvider, $priority = 100)
    {
        if (isset($this->providers[$priority])) {
            do {
                $priority++;
            } while (isset($this->providers[$priority]));
        }
        $this->providers[$priority] = $userDataProvider;
        $this->sorted = false;
    }

    /**
     * @return UserDataProviderInterface[]
     */
    public function getProviders(): array
    {
        if ($this->sorted === false) {
            krsort($this->providers);
            $this->sorted = true;
        }

        return $this->providers;
    }

    public function generate($userId)
    {
        $result = [];
        foreach ($this->getProviders() as $provider) {
            $data = $provider->data($userId);
            if ($data !== null) {
                $result[$provider::identifier()] = $provider->data($userId);
            }
        }
        return $result;
    }

    public function download($userId)
    {
        $result = [];
        foreach ($this->getProviders() as $provider) {
            $data = $provider->download($userId);
            if (!empty($data)) {
                $result[$provider::identifier()] = $data;
            }
        }
        return $result;
    }

    public function downloadAttachments($userId)
    {
        $result = [];
        foreach ($this->getProviders() as $provider) {
            $data = $provider->downloadAttachments($userId);
            if (!empty($data)) {
                $result = array_merge($result, $data);
            }
        }
        return $result;
    }

    public function protect($userId)
    {
        $protectedData = [];
        foreach ($this->getProviders() as $provider) {
            foreach ($provider->protect($userId) as $k => $v) {
                $data = (isset($protectedData[$k]) ? array_merge($protectedData[$k], $v) : $v);
                $protectedData[$k] = array_unique(array_filter($data));
            }
        }

        $this->protectedData = $protectedData;
    }

    public function canBeDeleted($userId): array
    {
        $errors = [];
        foreach ($this->getProviders() as $provider) {
            list($ok, $err) = $provider->canBeDeleted($userId);
            if (!$ok) {
                if (!is_array($err)) {
                    $err = [$err];
                }
                $errors = array_merge($errors, $err);
            }
        }
        if (!empty($errors)) {
            return [false, $errors];
        }
        return [true, null];
    }

    public function delete($userId)
    {
        foreach ($this->getProviders() as $provider) {
            $provider->delete($userId, ($this->protectedData[$provider::identifier()] ?? []));
        }
        return true;
    }
}
