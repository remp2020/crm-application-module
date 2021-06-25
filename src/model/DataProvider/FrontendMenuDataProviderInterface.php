<?php

namespace Crm\ApplicationModule\DataProvider;

use Crm\ApplicationModule\Menu\MenuContainerInterface;

interface FrontendMenuDataProviderInterface extends DataProviderInterface
{
    /**
     * @param array $params {
     *   @type MenuContainerInterface menuContainer
     * }
     */
    public function provide(array $params): void;
}
