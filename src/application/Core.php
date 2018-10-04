<?php

namespace Crm\ApplicationModule;

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;
use Nette\Configurator;
use Nette\Database\DriverException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Tomaj\Errbit\ErrbitLogger;

class Core
{
    /** @var Container */
    protected $container;

    protected $environment = null;

    public function __construct($appRootDir = null)
    {
        if (!defined('APP_ROOT')) {
            if (is_null($appRootDir)) {
                // TODO: [refactoring] try to solve this with some ENV / config variable? or change appRootDir to required?
                // working with assumption callers will be in placed in default crm-skeleton directories:
                // - <path-to-project>/bin/command.php
                // - <path-to-project>/app/bootstrap.php
                //$appRootDir = realpath(dirname($_SERVER["SCRIPT_FILENAME"]) . '/../');
                $appRootDir = realpath(__DIR__ . '/../../../../');
            }

            define('APP_ROOT', $appRootDir . '/');
        }
    }

    public function bootstrap(): Container
    {
        $this->init();
        $this->createContainer();
        $this->setDatabase();
        $this->setLogging();

        return $this->container;
    }

    public function command()
    {
        // TODO: [refactoring] try to find way around this?
        // Generated Nette cache directories will have incorrect permissions
        // if command was run before proper web user accesses app over http.
        // This ugly hack "fixes" it :\
        umask(0);

        $this->bootstrap();
        $application = new Application();
        $application->setCatchExceptions(false);

        new PhinxRegistrator(
            $application,
            $this->container->getByType(\Crm\ApplicationModule\EnvironmentConfig::class),
            $this->container->getByType(\Crm\ApplicationModule\ModuleManager::class)
        );

        try {
            /** @var ApplicationManager $applicationManager */
            $applicationManager = $this->container->getByType(\Crm\ApplicationModule\ApplicationManager::class);
            $commands = $applicationManager->getCommands();
            foreach ($commands as $command) {
                $application->add($command);
            }
        } catch (DriverException $driverException) {
            echo "INFO: Looks like the new fresh install.\n";
        } catch (InvalidArgumentException $invalidArgument) {
            echo "INFO: Looks like the new fresh install - or wrong configuration - '{$invalidArgument->getMessage()}'.\n";
        }

        return $application->run();
    }

    protected function init()
    {
        require_once APP_ROOT . 'vendor/autoload.php';

        $envFile = getenv('CRM_ENV_FILE') ?: '.env';
        $dotenv = new Dotenv(APP_ROOT, $envFile);
        $dotenv->load();
        $this->environment = getenv('CRM_ENV');
        if (!$this->environment) {
            die('You have to specify environment CRM_ENV');
        }

        if (getenv('CRM_FORCE_HTTPS') === 'true') {
            $_SERVER['HTTPS'] = true;
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $_SERVER['SERVER_PORT'] = 443;
        }
    }

    protected function createContainer()
    {
        $configurator = new Configurator;

        // set Nette DIR variables to proper directories (otherwise it leads to application-module path)
        $configurator->addParameters(array(
            'appDir' => APP_ROOT . 'app',
            'wwwDir' => APP_ROOT . 'www',
        ));

        if ($this->environment == 'local') {
            $configurator->setDebugMode(true);
        } else {
            $configurator->setDebugMode(false);
        }

        # terminal
        if (!isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SHELL'])) {
            $configurator->setDebugMode(true);
        }

        $configurator->enableDebugger(APP_ROOT . 'log');

        $configurator->setTempDirectory(APP_ROOT . 'temp/nette');

        $configurator->createRobotLoader()
            ->addDirectory(APP_ROOT . 'app')
            ->register();

        $configurator->addConfig(APP_ROOT . 'app/config/config.neon');
        $configurator->addConfig(APP_ROOT . 'app/config/config.' . $this->environment . '.neon');

        $this->container = $configurator->createContainer();
    }

    protected function setDatabase()
    {
        $database = $this->container->getByType('Nette\Database\Context');
        // TODO: [refactoring] test with proper settings 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'
        $database->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
        $database->query("SET NAMES utf8mb4");
    }

    protected function setLogging()
    {
        $errbitConfig = $this->container->parameters['errbit'];
        ErrbitLogger::register($errbitConfig);
    }
}
