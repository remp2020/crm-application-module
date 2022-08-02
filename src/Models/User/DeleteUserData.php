<?php

namespace Crm\ApplicationModule\User;

use Nette\Localization\Translator;
use Tracy\Debugger;
use Tracy\ILogger;

class DeleteUserData
{
    private $userDataRegistrator;

    private $translator;

    public function __construct(
        UserDataRegistrator $userDataRegistrator,
        Translator $translator
    ) {
        $this->userDataRegistrator = $userDataRegistrator;
        $this->translator = $translator;
    }

    public function canBeDeleted(int $userId): array
    {
        try {
            $canBeDeleted =  $this->userDataRegistrator->canBeDeleted($userId);
        } catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            $canBeDeleted = [false, [$this->translator->translate('application.user.delete_user_data.internal_error')]];
        }

        return $canBeDeleted;
    }

    /**
     * @param int $userId
     * @param bool $forceDelete If set to true, check if user can be removed is ignored. Default is false.
     * @throws \Exception Thrown when user cannot be deleted and $forceDelete is not set to true. Contains errors from providers.
     */
    public function deleteData(int $userId, bool $forceDelete = false): bool
    {
        if (!$forceDelete) {
            [$canBeDeleted, $errors] = $this->canBeDeleted($userId);
            if (!$canBeDeleted) {
                throw new \Exception(sprintf("cannot delete user {$userId}: %s", implode(', ', $errors)));
            }
        }

        $this->userDataRegistrator->protect($userId);
        return $this->userDataRegistrator->delete($userId);
    }
}
