<?php

namespace Crm\ApplicationModule\User;

class UserDataRegistrator
{
    /** @var UserDataProviderInterface[] */
    private $registrator = [];

    /** @var array */
    private $protectedData = [];

    public function addUserDataProvider(UserDataProviderInterface $userDataProvider)
    {
        $this->registrator[$userDataProvider::identifier()] = $userDataProvider;
    }

    public function generate($userId)
    {
        $result = [];
        foreach ($this->registrator as $key => $provider) {
            $result[$key] = $provider->data($userId);
        }
        return $result;
    }

    public function download($userId)
    {
        $result = [];
        foreach ($this->registrator as $key => $provider) {
            $data = $provider->download($userId);
            if (!empty($data)) {
                $result[$key] = $data;
            }
        }
        return $result;
    }

    public function downloadAttachments($userId)
    {
        $result = [];
        foreach ($this->registrator as $key => $provider) {
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
        foreach ($this->registrator as $key => $provider) {
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
        foreach ($this->registrator as $key => $provider) {
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
        foreach ($this->registrator as $key => $provider) {
            $provider->delete($userId, ($this->protectedData[$key] ?? []));
        }
        return true;
    }
}
