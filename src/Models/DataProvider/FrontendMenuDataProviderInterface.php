<?php

namespace Crm\ApplicationModule\Models\DataProvider;

use Crm\ApplicationModule\Models\Menu\MenuContainerInterface;

interface FrontendMenuDataProviderInterface extends DataProviderInterface
{
    /**
     * @param array{menuContainer: MenuContainerInterface} $params
     */
    public function provide(array $params): void;
}
