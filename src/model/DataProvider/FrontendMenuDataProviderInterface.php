<?php

namespace Crm\ApplicationModule\DataProvider;

use Crm\ApplicationModule\Menu\MenuContainerInterface;

interface FrontendMenuDataProviderInterface extends DataProviderInterface
{
    /**
     * @param array{menuContainer: MenuContainerInterface} $params
     */
    public function provide(array $params): void;
}
