<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Commands\DumpSchemaForTests;
use Phinx\Config\Config;
use Phinx\Console\Command\Create;
use Phinx\Console\Command\Init;
use Phinx\Console\Command\Migrate;
use Phinx\Console\Command\Rollback;
use Phinx\Console\Command\Status;
use Phinx\Console\Command\Test;
use Symfony\Component\Console\Application;

class PhinxRegistrator
{
    /** @var array Define phinx commands with aliases */
    private $commands = [
        Init::class => 'phinx:init',
        Create::class => 'phinx:create',
        Migrate::class => 'phinx:migrate',
        Rollback::class => 'phinx:rollback',
        Status::class => 'phinx:status',
        Test::class => 'phinx:test',
    ];

    /**
     * @param Application $application
     */
    public function __construct(
        Application $application,
        ModuleManager $moduleManager
    ) {
        // Configure module folders with migrations
        $modulesMigrations = [
            APP_ROOT . 'migrations',
        ];
        foreach ($moduleManager->getModules() as $module) {
            $reflector = new \ReflectionClass($module);
            $modulesMigrations[] = dirname($reflector->getFileName()) . '/migrations';
        }
        $config = self::buildConfig($modulesMigrations);

        // Register default phinx commands
        foreach ($this->commands as $class => $commandName) {
            $command = new $class;
            $command->setName($commandName);
            if (is_callable([$command, 'setConfig'])) {
                $command->setConfig($config);
            }
            $application->add($command);
        }

        // Register schema dump command. Command uses separate folder to dump migration into.
        $dumpSchema = new DumpSchemaForTests();
        $dumpSchema->setName('phinx:dump-schema-for-tests');
        $dumpSchema->setDescription('Dumps whole DB schema into single phinx migration file for tests');
        $dumpSchema->setConfig(self::buildTestConfig());
        $application->add($dumpSchema);
    }

    public static function buildConfig($migrationPaths): Config
    {
        $env = Core::env('CRM_ENV');

        $configData = [
            'paths' => [
                'migrations' => $migrationPaths,
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_environment' => $env,
            ],
        ];

        $configData['environments'][$env] = self::buildDbConfig();
        return new Config($configData, __FILE__);
    }

    public static function buildTestConfig(): Config
    {
        $env = Core::env('CRM_ENV');

        $configData = [
            'paths' => [
                'migrations' => APP_ROOT . 'tests/migrations',
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => $env,
            ],

            // configuration of odan/phinx-migrations-generator; not interfering with default phinx options
            'schema_file' => APP_ROOT . 'tests/migrations/schema.php',
            'foreign_keys' => true,
            'mark_generated_migration' => false,
        ];

        $configData['environments'][$env] = self::buildDbConfig();
        return new Config($configData, __FILE__);
    }

    public static function buildDbConfig()
    {
        return [
            'adapter' => Core::env('CRM_DB_ADAPTER'),
            'host' => Core::env('CRM_DB_HOST'),
            'name' => Core::env('CRM_DB_NAME'),
            'user' => Core::env('CRM_DB_USER'),
            'pass' => Core::env('CRM_DB_PASS'),
            'port' => Core::env('CRM_DB_PORT'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    }
}
