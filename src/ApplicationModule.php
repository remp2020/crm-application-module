<?php

namespace Crm\ApplicationModule;

use Crm\ApiModule\Models\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Models\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Models\Router\ApiIdentifier;
use Crm\ApiModule\Models\Router\ApiRoute;
use Crm\ApplicationModule\Api\EventGeneratorsListApiHandler;
use Crm\ApplicationModule\Api\EventsListApiHandler;
use Crm\ApplicationModule\Application\CommandsContainerInterface;
use Crm\ApplicationModule\Application\Managers\AssetsManager;
use Crm\ApplicationModule\Application\Managers\LayoutManager;
use Crm\ApplicationModule\Application\Managers\SeederManager;
use Crm\ApplicationModule\Commands\AuditLogsCleanupCommand;
use Crm\ApplicationModule\Commands\BigintMigrationCleanupCommand;
use Crm\ApplicationModule\Commands\CacheCommand;
use Crm\ApplicationModule\Commands\CalculateMeasurementsCommand;
use Crm\ApplicationModule\Commands\CleanupCommand;
use Crm\ApplicationModule\Commands\DatabaseSeedCommand;
use Crm\ApplicationModule\Commands\GenerateKeyCommand;
use Crm\ApplicationModule\Commands\HeartbeatCommand;
use Crm\ApplicationModule\Commands\HermesShutdownCommand;
use Crm\ApplicationModule\Commands\HermesWorkerCommand;
use Crm\ApplicationModule\Commands\InstallAssetsCommand;
use Crm\ApplicationModule\Commands\MigrateAuditLogsCommand;
use Crm\ApplicationModule\Commands\PopulatorCommand;
use Crm\ApplicationModule\Hermes\HeartbeatMysql;
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
        $commandsContainer->registerCommand($this->getInstance(DatabaseSeedCommand::class));
        if ($this->hasInstance('populator')) {
            $commandsContainer->registerCommand($this->getInstance(PopulatorCommand::class));
        }

        $commandsContainer->registerCommand($this->getInstance(HeartbeatCommand::class));
        $commandsContainer->registerCommand($this->getInstance(HermesShutdownCommand::class));
        $commandsContainer->registerCommand($this->getInstance(HermesWorkerCommand::class));
        $commandsContainer->registerCommand($this->getInstance(CleanupCommand::class));
        $commandsContainer->registerCommand($this->getInstance(CacheCommand::class));
        $commandsContainer->registerCommand($this->getInstance(InstallAssetsCommand::class));
        $commandsContainer->registerCommand($this->getInstance(GenerateKeyCommand::class));
        $commandsContainer->registerCommand($this->getInstance(CalculateMeasurementsCommand::class));
        $commandsContainer->registerCommand($this->getInstance(AuditLogsCleanupCommand::class));
        $commandsContainer->registerCommand($this->getInstance(MigrateAuditLogsCommand::class));
        $commandsContainer->registerCommand($this->getInstance(BigintMigrationCleanupCommand::class));
    }

    public function registerHermesHandlers(Dispatcher $dispatcher)
    {
        $dispatcher->registerHandler('heartbeat', $this->getInstance(HeartbeatMysql::class));
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(new ApiRoute(
            new ApiIdentifier('1', 'events', 'list'),
            EventsListApiHandler::class,
            BearerTokenAuthorization::class
            // \Tomaj\NetteApi\Authorization\BearerTokenAuthorization::class
        ));

        $apiRoutersContainer->attachRouter(new ApiRoute(
            new ApiIdentifier('1', 'event-generators', 'list'),
            EventGeneratorsListApiHandler::class,
            BearerTokenAuthorization::class
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
        $installAssetsCommand = $this->getInstance(InstallAssetsCommand::class);
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
