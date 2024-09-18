<?php

namespace Crm\ApplicationModule\Models\DataProvider;

interface DefaultDataProviderInterface extends DataProviderInterface
{
    public function provide(array $params);
}
