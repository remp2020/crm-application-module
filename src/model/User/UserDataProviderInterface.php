<?php

namespace Crm\ApplicationModule\User;

interface UserDataProviderInterface
{
    public static function identifier(): string;

    public function data($userId);

    public function download($userId);

    public function downloadAttachments($userId);

    public function protect($userId): array;

    /**
     * canBeDeleted returns array with two values, boolean whether user can be deleted or not and list of string error
     * messages explaining why user cannot be deleted
     *
     * @param $userId
     * @return array
     */
    public function canBeDeleted($userId): array;

    public function delete($userId, $protectedData = []);
}
