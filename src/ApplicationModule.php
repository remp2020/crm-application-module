<?php

namespace Crm\ApplicationModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApplicationModule\Commands\CommandsContainerInterface;
use Crm\ApplicationModule\Seeders\CalendarSeeder;
use Crm\ApplicationModule\Seeders\ConfigsSeeder;
use Crm\ApplicationModule\Seeders\CountriesSeeder;
use Crm\ApplicationModule\Seeders\SnippetsSeeder;
use Tomaj\Hermes\Dispatcher;
use Tracy\Debugger;
use Tracy\ILogger;

class ApplicationModule extends CrmModule
{
    const COPY_ASSETS_CHECK_FILE = 'copy_assets_check';

    public function registerCommands(CommandsContainerInterface $commandsContainer)
    {
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\DatabaseSeedCommand::class));
        if ($this->hasInstance('populator')) {
            $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\PopulatorCommand::class));
        }

        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\HeartbeatCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\HermesShutdownCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\HermesWorkerCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\CleanupCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\CacheCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\InstallAssetsCommand::class));
    }

    public function registerHermesHandlers(Dispatcher $dispatcher)
    {
        $dispatcher->registerHandler('heartbeat', $this->getInstance(\Crm\ApplicationModule\Hermes\HeartbeatMysql::class));
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(new ApiRoute(
            new ApiIdentifier('1', 'events', 'list'),
            \Crm\ApplicationModule\Api\EventsListApiHandler::class,
            \Crm\ApiModule\Authorization\BearerTokenAuthorization::class
        ));

        $apiRoutersContainer->attachRouter(new ApiRoute(
            new ApiIdentifier('1', 'event-generators', 'list'),
            \Crm\ApplicationModule\Api\EventGeneratorsListApiHandler::class,
            \Crm\ApiModule\Authorization\BearerTokenAuthorization::class
        ));
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

    public function registerAssets(AssetsManager $assetsManager)
    {
        $assetsManager->copyAssets(__DIR__ . '/../assets/' . self::COPY_ASSETS_CHECK_FILE, self::COPY_ASSETS_CHECK_FILE);

        if (!$assetsManager->checkAssetsFileExist(self::COPY_ASSETS_CHECK_FILE)) {
            Debugger::log("Module assets are not installed yet, please run 'application:install_assets' CRM command", ILogger::WARNING);
            return;
        }

        $assetsManager->copyAssets(__DIR__ . '/../assets/js', 'layouts/application/js');
    }
}
