<?php

namespace Crm\ApplicationModule\Models\Access;

interface ProviderInterface
{
    public function hasAccess($userId, $access);

    public function available($access);
}
