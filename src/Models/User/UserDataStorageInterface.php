<?php

namespace Crm\ApplicationModule\Models\User;

interface UserDataStorageInterface
{
    public function load($token);

    public function multiLoad(array $tokens);

    public function store($token, $data);

    public function multiStore(array $tokens, $data);

    public function remove($token);

    public function multiRemove(array $tokens);

    /**
     * iterateTokens iterates every matched token in the user_data storage and passes it to callback along with its
     * user data as $callback($token, $userData).
     */
    public function iterateTokens(callable $callback);
}
