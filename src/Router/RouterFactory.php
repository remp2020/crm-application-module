<?php

namespace Crm\ApplicationModule\Router;

use Crm\ApplicationModule\Application\Managers\ModuleManager;
use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\ApplicationModule\Models\Config\ConfigsCache;
use Nette\Application\Routers\RouteList;

/**
 * Router factory.
 */
class RouterFactory
{
    private $configsCache;

    private $applicationConfig;

    private $moduleManager;

    public function __construct(
        ConfigsCache $configsCache,
        ApplicationConfig $applicationConfig,
        ModuleManager $moduleManager,
    ) {
        $this->configsCache = $configsCache;
        $this->applicationConfig = $applicationConfig;
        $this->moduleManager = $moduleManager;
    }

    public function createRouter(): RouteList
    {
        $router = new RouteList();

        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerRoutes($router);
        }

        $defaultRoute = $this->configsCache->get('default_route');
        if (!$defaultRoute) {
            $defaultRoute = $this->applicationConfig->get('default_route');
            $this->configsCache->add('default_route', $defaultRoute);
        }
        if (!$defaultRoute) {
            $defaultRoute = 'Application:Default:default';
        }

        $router->addRoute('admin/', 'Dashboard:Dashboard:default');
        $router->addRoute('snippets[/<key>]', 'Application:Snippets:default');
        $router->addRoute('<module>/<presenter>/<action>[/<id>]', 'Dashboard:Dashboard:default');
        $router->addRoute('/', $defaultRoute);

        return $router;
    }
}
