<?php

namespace Crm\ApplicationModule\Access;

class TestAccessProvider implements ProviderInterface
{
    public function hasAccess($userId, $access)
    {
        return true;
    }

    public function available($access)
    {
        return true;
    }
}
