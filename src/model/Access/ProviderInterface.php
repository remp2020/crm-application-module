<?php

namespace Crm\ApplicationModule\Access;

interface ProviderInterface
{
    public function hasAccess($userId, $access);

    public function available($access);
}
