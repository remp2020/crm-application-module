<?php

namespace Crm\ApplicationModule;

use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApplicationModule\Commands\CommandsContainerInterface;
use Crm\ApplicationModule\Seeders\CalendarSeeder;
use Crm\ApplicationModule\Seeders\ConfigsSeeder;
use Crm\ApplicationModule\Seeders\CountriesSeeder;
use Crm\ApplicationModule\Seeders\SnippetsSeeder;
use League\Event\Emitter;
use Nette\DI\Container;

class ApplicationModule extends CrmModule
{
    public function registerCommands(CommandsContainerInterface $commandsContainer)
    {
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\DatabaseSeedCommand::class));
        if ($this->hasInstance('populator')) {
            $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\PopulatorCommand::class));
        }

        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\HermesWorkerCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\CleanupCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\CacheCommand::class));
    }

    public function registerCleanupFunction(CallbackManagerInterface $cleanUpManager)
    {
        $cleanUpManager->add(function (Container $container) {
            //            $hermesTaskRepository = $container->getByType(\Crm\ApplicationModule\Repository\HermesTasksRepository::class);
//            $hermesTaskRepository->removeOldData();
        });
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(new ApiIdentifier('1', 'users', 'data'), \Crm\ApplicationModule\Api\UserDataHandler::class, \Crm\ApiModule\Authorization\NoAuthorization::class)
        );
    }

    // TODO: [users_module] application module by nemal mat ziadny event handler, aby neexistovala zavislost na ostatnych moduloch
    public function registerEventHandlers(Emitter $emitter)
    {
        $emitter->addListener(
            \Crm\UsersModule\Events\NewAccessTokenEvent::class,
            $this->getInstance(\Crm\ApplicationModule\Events\NewAccessTokenHandler::class)
        );
        $emitter->addListener(
            \Crm\UsersModule\Events\RemovedAccessTokenEvent::class,
            $this->getInstance(\Crm\ApplicationModule\Events\RemovedAccessTokenHandler::class)
        );
    }

    public function registerLayouts(LayoutManager $layoutManager)
    {
        $layoutManager->registerLayout('frontend', realpath(__DIR__ . '/templates/@frontend_layout.latte'));
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(CalendarSeeder::class));
        $seederManager->addSeeder($this->getInstance(ConfigsSeeder::class));
        $seederManager->addSeeder($this->getInstance(CountriesSeeder::class));
        $seederManager->addSeeder($this->getInstance(SnippetsSeeder::class));
    }
}
