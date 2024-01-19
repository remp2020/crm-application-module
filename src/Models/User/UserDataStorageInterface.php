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
}
