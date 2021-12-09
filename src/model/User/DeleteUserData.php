<?php

namespace Crm\ApplicationModule\User;

use Nette\Localization\ITranslator;
use Tracy\Debugger;
use Tracy\ILogger;

class DeleteUserData
{
    private $userDataRegistrator;

    private $translator;

    public function __construct(
        UserDataRegistrator $userDataRegistrator,
        ITranslator $translator
    ) {
        $this->userDataRegistrator = $userDataRegistrator;
        $this->translator = $translator;
    }

    public function canBeDeleted($userId): array
    {
        try {
            $canBeDeleted =  $this->userDataRegistrator->canBeDeleted($userId);
        } catch (\Exception $e) {
            Debugger::log($e->getMessage(), ILogger::EXCEPTION);
            $canBeDeleted = [false, [$this->translator->translate('application.user.delete_user_data.internal_error')]];
        }

        return $canBeDeleted;
    }

    public function deleteData($userId)
    {
        [$canBeDeleted, $errors] = $this->canBeDeleted($userId);
        if (!$canBeDeleted) {
            throw new \Exception(sprintf("cannot delete user {$userId}: %s", implode(', ', $errors)));
        }
        $this->userDataRegistrator->protect($userId);
        return $this->userDataRegistrator->delete($userId);
    }
}
