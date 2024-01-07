<?php

namespace Crm\ApplicationModule;

use Crm\ApiModule\Models\Router\ApiRoutesContainer;
use Crm\ApplicationModule\Access\AccessManager;
use Crm\ApplicationModule\Authenticator\AuthenticatorManager;
use Crm\ApplicationModule\Commands\CommandsContainer;
use Crm\ApplicationModule\Criteria\CriteriaStorage;
use Crm\ApplicationModule\Criteria\ScenariosCriteriaStorage;
use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Event\EventsStorage;
use Crm\ApplicationModule\Menu\MenuContainer;
use Crm\ApplicationModule\User\UserDataRegistrator;
use Crm\ApplicationModule\Widget\LazyWidgetManager;
use Crm\ApplicationModule\Widget\WidgetManager;
use League\Event\Emitter;
use Tomaj\Hermes\Dispatcher;

// TODO: [users_module] popremyslat, ci nevieme prepisat inak, je tu zavislost na apiRoutesContainer a userDataRegistrator
// TODO: [users_module] v moduloch chceme registrovat veci, ktore tu nie su vymenovane
class ApplicationManager
{
    private $moduleManager;

    private $adminMenu;

    private $frontendMenu;

    private $emitter;

    private $widgetManager;

    private $commandsContainer;

    private $apiRoutesContainer;

    private $authenticatorManager;

    private $cleanupCallbacks;

    private $userDataRegistrator;

    private $dispatcher;

    private $criteriaStorage;

    private $layoutManager;

    private $seederManager;

    private $accessManager;

    private $dataProviderManager;

    private $eventsStorage;

    private $scenariosCriteriaStorage;

    private $assetsManager;

    private LazyWidgetManager $lazyWidgetManager;

    public function __construct(
        Emitter $emitter,
        ModuleManager $moduleManager,
        WidgetManager $widgetManager,
        ApiRoutesContainer $apiRoutesContainer,
        AuthenticatorManager $authenticatorManager,
        CleanUpManager $cleanUpManager,
        UserDataRegistrator $userDataRegistrator,
        Dispatcher $dispatcher,
        CriteriaStorage $criteriaStorage,
        ScenariosCriteriaStorage $scenariosCriteriaStorage,
        LayoutManager $layoutManager,
        SeederManager $seederManager,
        AccessManager $accessManager,
        AssetsManager $assetsManager,
        DataProviderManager $dataProviderManager,
        EventsStorage $eventsStorage,
        LazyWidgetManager $lazyWidgetManager
    ) {
        $this->widgetManager = $widgetManager;
        $this->emitter = $emitter;
        $this->commandsContainer = new CommandsContainer();
        $this->apiRoutesContainer = $apiRoutesContainer;
        $this->authenticatorManager = $authenticatorManager;
        $this->cleanupCallbacks = $cleanUpManager;
        $this->userDataRegistrator = $userDataRegistrator;
        $this->dispatcher = $dispatcher;
        $this->criteriaStorage = $criteriaStorage;
        $this->moduleManager = $moduleManager;
        $this->layoutManager = $layoutManager;
        $this->seederManager = $seederManager;
        $this->accessManager = $accessManager;
        $this->dataProviderManager = $dataProviderManager;
        $this->eventsStorage = $eventsStorage;
        $this->scenariosCriteriaStorage = $scenariosCriteriaStorage;
        $this->assetsManager = $assetsManager;
        $this->lazyWidgetManager = $lazyWidgetManager;
    }

    public function registerEventHandlers()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerEventHandlers($this->emitter);
        }
    }

    public function registerLazyEventHandlers()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerLazyEventHandlers($this->emitter);
        }
    }

    public function getAdminMenuItems()
    {
        if (!$this->adminMenu) {
            $this->loadAdminMenu();
        }
        return $this->adminMenu;
    }

    private function loadAdminMenu()
    {
        $this->adminMenu = new MenuContainer();
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerAdminMenuItems($this->adminMenu);
        }
    }

    public function getFrontendMenuContainer()
    {
        if (!$this->frontendMenu) {
            $this->loadFrontendMenu();
        }
        return $this->frontendMenu;
    }

    private function loadFrontendMenu()
    {
        $this->frontendMenu = new MenuContainer();
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerFrontendMenuItems($this->frontendMenu);
        }
    }

    /**
     * @deprecated use registerLazyWidget() instead
     */
    public function registerWidgets()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerWidgets($this->widgetManager);
        }
    }

    public function registerLazyWidget()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerLazyWidgets($this->lazyWidgetManager);
        }
    }

    public function registerCommands()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerCommands($this->commandsContainer);
        }
    }

    public function getCommands()
    {
        return $this->commandsContainer->getCommands();
    }

    public function registerApiCalls()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerApiCalls($this->apiRoutesContainer);
        }
    }

    public function registerCleanupCallbacks()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerCleanupFunction($this->cleanupCallbacks);
        }
    }

    public function registerHermesHandlers()
    {
        /** @var ApplicationModuleInterface $module */
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerHermesHandlers($this->dispatcher);
        }
    }

    public function registerAuthenticators()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerAuthenticators($this->authenticatorManager);
        }
    }

    public function registerUserDataRegistrators()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerUserData($this->userDataRegistrator);
        }
    }

    public function registerCriteriaStorage()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerSegmentCriteria($this->criteriaStorage);
        }
    }

    public function registerScenariosCriteriaStorage()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerScenariosCriteria($this->scenariosCriteriaStorage);
        }
    }

    public function registerLayouts()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerLayouts($this->layoutManager);
        }
    }

    public function registerSeeders()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerSeeders($this->seederManager);
        }
    }

    public function registerAssets()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerAssets($this->assetsManager);
        }
    }

    public function registerAccessProviders()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerAccessProvider($this->accessManager);
        }
    }

    public function registerDataProviders()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerDataProviders($this->dataProviderManager);
        }
    }

    public function registerEvents()
    {
        foreach ($this->moduleManager->getModules() as $module) {
            $module->registerEvents($this->eventsStorage);
        }
    }

    public function initialize()
    {
        if (Core::isCli()) {
            $this->registerCommands();
            $this->registerCleanupCallbacks();
            $this->registerHermesHandlers();
            $this->registerSeeders();
            $this->registerAssets();
        } elseif (!Request::isApi()) {
            $this->registerWidgets();
            $this->registerLayouts();
            $this->registerLazyWidget();
        }

        $this->registerEventHandlers();
        $this->registerLazyEventHandlers();
        $this->registerApiCalls();
        $this->registerAuthenticators();
        $this->registerUserDataRegistrators();
        $this->registerCriteriaStorage();
        $this->registerScenariosCriteriaStorage();
        $this->registerAccessProviders();
        $this->registerDataProviders();
        $this->registerEvents();
    }
}
