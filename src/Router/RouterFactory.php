<?php

namespace Crm\ApplicationModule\Router;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\ConfigsCache;
use Crm\ApplicationModule\ModuleManager;
use Nette\Application\Routers\Route;
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
        ModuleManager $moduleManager
    ) {
        $this->configsCache = $configsCache;
        $this->applicationConfig = $applicationConfig;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
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

        $router[] = new Route('admin/', 'Dashboard:Dashboard:default');
        $router[] = new Route('snippets[/<key>]', 'Application:Snippets:default');
        $router[] = new Route('<module>/<presenter>/<action>[/<id>]', 'Dashboard:Dashboard:default');
        $router[] = new Route('/', $defaultRoute);

        return $router;
    }
}
