<?php

namespace Crm\ApplicationModule\Application;

use Crm\ApiModule\Models\Api\ApiRoutersContainerInterface;
use Crm\ApplicationModule\Application\Managers\AssetsManager;
use Crm\ApplicationModule\Application\Managers\CallbackManagerInterface;
use Crm\ApplicationModule\Application\Managers\LayoutManager;
use Crm\ApplicationModule\Application\Managers\SeederManager;
use Crm\ApplicationModule\Models\Access\AccessManager;
use Crm\ApplicationModule\Models\Authenticator\AuthenticatorManagerInterface;
use Crm\ApplicationModule\Models\Criteria\CriteriaStorage;
use Crm\ApplicationModule\Models\Criteria\ScenariosCriteriaStorage;
use Crm\ApplicationModule\Models\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Models\Event\EventsStorage;
use Crm\ApplicationModule\Models\Event\LazyEventEmitter;
use Crm\ApplicationModule\Models\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Models\Scenario\TriggerManager;
use Crm\ApplicationModule\Models\User\UserDataRegistrator;
use Crm\ApplicationModule\Models\Widget\LazyWidgetManagerInterface;
use League\Event\Emitter;
use Nette\Application\Routers\RouteList;
use Symfony\Component\Console\Output\OutputInterface;
use Tomaj\Hermes\Dispatcher;

interface ApplicationModuleInterface
{
    public function registerAdminMenuItems(MenuContainerInterface $menuContainer);

    public function registerFrontendMenuItems(MenuContainerInterface $menuContainer);

    public function registerEventHandlers(Emitter $emitter);

    public function registerLazyEventHandlers(LazyEventEmitter $lazyEventEmitter);

    public function registerLazyWidgets(LazyWidgetManagerInterface $lazyWidgetManager);

    public function registerCommands(CommandsContainerInterface $commandsContainer);

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer);

    public function registerCleanupFunction(CallbackManagerInterface $cleanUpManager);

    public function registerHermesHandlers(Dispatcher $dispatcher);

    public function registerAuthenticators(AuthenticatorManagerInterface $authenticatorManager);

    public function registerUserData(UserDataRegistrator $dataRegistrator);

    public function registerSegmentCriteria(CriteriaStorage $criteriaStorage);

    public function registerScenariosTriggers(TriggerManager $triggerManager);

    public function registerScenariosCriteria(ScenariosCriteriaStorage $scenariosCriteriaStorage);

    public function registerRoutes(RouteList $router);

    public function cache(OutputInterface $output, array $tags = []);

    public function registerLayouts(LayoutManager $layoutManager);

    public function registerSeeders(SeederManager $seederManager);

    public function registerAssets(AssetsManager $assetsManager);

    public function registerAccessProvider(AccessManager $accessManager);

    public function registerDataProviders(DataProviderManager $dataProvider);

    /**
     * @throws \Exception
     */
    public function registerEvents(EventsStorage $criteriaStorage);
}
