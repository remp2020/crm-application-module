<?php

namespace Crm\ApplicationModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApplicationModule\Access\AccessManager;
use Crm\ApplicationModule\Authenticator\AuthenticatorManagerInterface;
use Crm\ApplicationModule\Commands\CommandsContainerInterface;
use Crm\ApplicationModule\Criteria\CriteriaStorage;
use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Event\EventsStorage;
use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Crm\ApplicationModule\User\UserDataRegistrator;
use Crm\ApplicationModule\Widget\WidgetManagerInterface;
use Kdyby\Translation\Translator;
use League\Event\Emitter;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Tomaj\Hermes\Dispatcher;

abstract class CrmModule implements ApplicationModuleInterface
{
    /** @var  Container */
    private $container;

    protected $translator;

    public function __construct(Container $container, Translator $translator)
    {
        $this->container = $container;
        $this->translator = $translator;
    }

    protected function getInstance($type)
    {
        return $this->container->getByType($type);
    }

    protected function hasInstance($type)
    {
        return $this->container->hasService($type);
    }

    public function registerAdminMenuItems(MenuContainerInterface $menuContainer)
    {
        // nothing
    }

    public function registerFrontendMenuItems(MenuContainerInterface $menuContainer)
    {
        // nothing
    }

    public function registerEventHandlers(Emitter $emitter)
    {
        // nothing
    }

    public function registerWidgets(WidgetManagerInterface $widgetManager)
    {
        // nothing
    }

    public function registerCommands(CommandsContainerInterface $commandsContainer)
    {
        // nothing
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        // nothing
    }

    public function registerCleanupFunction(CallbackManagerInterface $cleanUpManager)
    {
        // nothing
    }

    public function registerHermesHandlers(Dispatcher $dispatcher)
    {
        // nothing
    }

    public function registerAuthenticators(AuthenticatorManagerInterface $authenticatorManager)
    {
        // nothing
    }

    public function registerUserData(UserDataRegistrator $dataRegistrator)
    {
        // nothing
    }

    public function registerSegmentCriteria(CriteriaStorage $criteriaStorage)
    {
        // nothing
    }

    public function registerRoutes(RouteList $router)
    {
        // nothing
    }

    public function cache(OutputInterface $output, array $tags = [])
    {
        // nothing
    }

    public function registerLayouts(LayoutManager $layoutManager)
    {
        // nothing
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        // nothing
    }

    public function registerAccessProvider(AccessManager $accessManager)
    {
        // nothing
    }

    public function registerDataProviders(DataProviderManager $dataProviderManager)
    {
        // nothing
    }

    public function registerEvents(EventsStorage $eventsStorage)
    {
        // nothing
    }
}
