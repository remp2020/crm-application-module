<?php

namespace Crm\ApplicationModule\User;

use Crm\UsersModule\Repository\AccessTokensRepository;
use Nette\Utils\Json;

// TODO: [users_module] toto by sa mohlo mozno presunut do UsersModule, aby neexistovala zavislost medzi Application a AccessTokens
class UserData
{
    private $userDataRegistrator;

    private $userDataStorage;

    private $accessTokensRepository;

    public function __construct(
        UserDataRegistrator $userDataRegistrator,
        UserDataStorageInterface $userDataStorage,
        AccessTokensRepository $accessTokensRepository
    ) {
        $this->userDataRegistrator = $userDataRegistrator;
        $this->userDataStorage = $userDataStorage;
        $this->accessTokensRepository = $accessTokensRepository;
    }

    public function refreshUserTokens($userId)
    {
        $userDataContent = $this->userDataRegistrator->generate($userId);
        $tokens = $this->accessTokensRepository->allUserTokens($userId);

        $tokensString = [];
        foreach ($tokens as $token) {
            $tokensString[] = $token->token;
        }
        $this->userDataStorage->multiStore($tokensString, Json::encode($userDataContent));
    }
    
    public function refreshUserToken($userId, $token)
    {
        $userDataContent = $this->userDataRegistrator->generate($userId);
        $this->userDataStorage->store($token, Json::encode($userDataContent));
    }

    public function getUserToken($token)
    {
        $data = $this->userDataStorage->load($token);
        if ($data) {
            return Json::decode($data);
        }
        return false;
    }

    public function getUserTokens(array $tokens)
    {
        $data = $this->userDataStorage->multiLoad($tokens);
        $result = [];
        foreach ($data as $row) {
            if ($row !== null) {
                $result[] = Json::decode($row);
            }
        }
        return $result;
    }

    public function removeUserToken($token)
    {
        return $this->userDataStorage->remove($token);
    }
}
