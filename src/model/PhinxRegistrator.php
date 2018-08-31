<?php

namespace Crm\ApplicationModule;

use Phinx\Config\Config;
use Symfony\Component\Console\Application;

class PhinxRegistrator
{
    /** @var EnvironmentConfig  */
    private $envConfig;

    /** @var array                  Define phinx commands with aliases */
    private $command = [
        '\Phinx\Console\Command\Init' => 'phinx:init',
        '\Phinx\Console\Command\Create' => 'phinx:create',
        '\Phinx\Console\Command\Migrate' => 'phinx:migrate',
        '\Phinx\Console\Command\Rollback' => 'phinx:rollback',
        '\Phinx\Console\Command\Status' => 'phinx:status',
        '\Phinx\Console\Command\Test' => 'phinx:test'
    ];

    private $moduleManager;

    /**
     * @param Application $application
     */
    public function __construct(
        Application $application,
        EnvironmentConfig $envConfig,
        ModuleManager $moduleManager
    ) {
        $this->envConfig = $envConfig;
        $this->moduleManager = $moduleManager;
        $config = new Config($this->buildConfig(), __FILE__);

        // Register all commands
        foreach ($this->command as $class => $commandName) {
            $command = new $class;
            $command->setName($commandName);
            if (is_callable([$command, 'setConfig'])) {
                $command->setConfig($config);
            }
            $application->add($command);
        }
    }

    /**
     * Build phinx config from config.local.neon
     * @return array
     */
    private function buildConfig()
    {
        $env = getenv('CRM_ENV');

        $configData = [
            'paths' => [
                'migrations' => [
                    '%%PHINX_CONFIG_DIR%%/../../../../migrations'
                ]
            ],
            'environments' => [
                'default_migration_table' => 'phinxlog',
                'default_database' => $env,
            ],
        ];

        foreach ($this->moduleManager->getModules() as $module) {
            $reflector = new \ReflectionClass($module);
            $configData['paths']['migrations'][] = dirname($reflector->getFileName()) . '/migrations';
        }

        $configData['environments'][$env] = [
            'adapter' => $this->envConfig->get('CRM_DB_ADAPTER'),
            'host' => $this->envConfig->get('CRM_DB_HOST'),
            'name' => $this->envConfig->get('CRM_DB_NAME'),
            'user' => $this->envConfig->get('CRM_DB_USER'),
            'pass' => $this->envConfig->get('CRM_DB_PASS'),
            'port' => $this->envConfig->get('CRM_DB_PORT'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        return $configData;
    }
}
