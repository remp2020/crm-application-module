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
use Symfony\Component\Console\Command\Command;
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
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\GenerateKeyCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\CalculateMeasurementsCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\AuditLogsCleanupCommand::class));
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApplicationModule\Commands\MigrateAuditLogsCommand::class));
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
            // \Tomaj\NetteApi\Authorization\BearerTokenAuthorization::class
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
        $assetsManager->copyAssets(__DIR__ . '/assets/' . self::COPY_ASSETS_CHECK_FILE, self::COPY_ASSETS_CHECK_FILE);

        /** @var Command $installAssetsCommand */
        $installAssetsCommand = $this->getInstance(\Crm\ApplicationModule\Commands\InstallAssetsCommand::class);
        $cmd = $_SERVER['argv'][0] ?? null;
        $arg = $_SERVER['argv'][1] ?? null;

        if (!$assetsManager->checkAssetsFileExist(self::COPY_ASSETS_CHECK_FILE)
            && $arg !== $installAssetsCommand->getName() // command actually installing the assets
            && strpos($cmd, 'composer') === false // composer hooks
        ) {
            Debugger::log("Module assets are not installed yet, please run '{$installAssetsCommand->getName()}' CRM command", ILogger::WARNING);
            return;
        }

        $assetsManager->copyAssets(__DIR__ . '/assets/js', 'layouts/application/js');
    }
}
