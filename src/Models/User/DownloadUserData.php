<?php

namespace Crm\ApplicationModule\Models\User;

class DownloadUserData
{
    private $userDataRegistrator;

    public function __construct(
        UserDataRegistrator $userDataRegistrator,
    ) {
        $this->userDataRegistrator = $userDataRegistrator;
    }

    public function getData($userId)
    {
        return $this->userDataRegistrator->download($userId);
    }

    public function getAttachments($userId)
    {
        return $this->userDataRegistrator->downloadAttachments($userId);
    }
}
