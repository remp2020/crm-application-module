<?php

namespace Crm\ApplicationModule\User;

class DeleteUserData
{
    private $userDataRegistrator;

    public function __construct(
        UserDataRegistrator $userDataRegistrator
    ) {
        $this->userDataRegistrator = $userDataRegistrator;
    }

    public function canBeDeleted($userId): array
    {
        return $this->userDataRegistrator->canBeDeleted($userId);
    }

    public function deleteData($userId)
    {
        list($canBeDeleted, $errors) = $this->canBeDeleted($userId);
        if (!$canBeDeleted) {
            throw new \Exception(sprintf("cannot delete user {$userId}: %s", implode(', ', $errors)));
        }
        $this->userDataRegistrator->protect($userId);
        return $this->userDataRegistrator->delete($userId);
    }
}
