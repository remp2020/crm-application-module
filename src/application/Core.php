<?php

namespace Crm\ApplicationModule;

use Dotenv\Dotenv;
use Nette\Configurator;
use Nette\Database\DriverException;
use Nette\DI\Container;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\InvalidArgumentException;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Application;

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
                $appRootDir = realpath(dirname($_SERVER["SCRIPT_FILENAME"]) . '/../');
            }

            define('APP_ROOT', $appRootDir . DIRECTORY_SEPARATOR);
        }
    }

    public function bootstrap(): Container
    {
        // TODO: [refactoring] try to find way around this?
        // Generated Nette cache directories will have incorrect permissions
        // if command was run before proper web user accesses app over http.
        // This ugly hack "fixes" it :\
        umask(0);

        $this->init();
        $this->createContainer();
        $this->setDatabase();

        return $this->container;
    }

    public function command()
    {
        $this->bootstrap();
        $application = new Application();
        $application->setCatchExceptions(false);

        new PhinxRegistrator(
            $application,
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

        $this->loadEnv();

        $this->environment = self::env('CRM_ENV');

        if (self::env('CRM_FORCE_HTTPS') === 'true') {
            $_SERVER['HTTPS'] = 'on';
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $_SERVER['SERVER_PORT'] = 443;
        }
    }

    private static function getEnvFilePath(): string
    {
        return self::env('CRM_ENV_FILE') ?: '.env';
    }

    private function loadEnv(): void
    {
        $envFile = self::getEnvFilePath();
        $dotenv = Dotenv::createImmutable(APP_ROOT, $envFile);
        $dotenv->load();
        $dotenv->required('CRM_ENV');
        $dotenv->ifPresent('CRM_FORCE_HTTPS')->isBoolean();
        $dotenv->required(['CRM_DB_ADAPTER', 'CRM_DB_HOST', 'CRM_DB_NAME', 'CRM_DB_USER', 'CRM_DB_PASS']);
    }

    public static function writeEnv(string $key, string $value): void
    {
        if ($key !== 'CRM_KEY') {
            throw new \Exception("Unable to write key '$key' to .env file, only CRM_KEY is allowed.");
        }

        $escaped = preg_quote('=' . Core::env($key, ''), '/');
        $replacementPattern = "/^{$key}{$escaped}/m";

        $count = 0;
        $envContent = preg_replace(
            $replacementPattern,
            $key . '=' . $value,
            FileSystem::read(self::getEnvFilePath()),
            -1,
            $count
        );

        if ($count === 0) {
            // key was not present, therefore append it as a new line
            $envContent .= PHP_EOL . $key . '=' . $value . PHP_EOL;
        }

        FileSystem::write(self::getEnvFilePath(), $envContent);
    }

    protected function createContainer()
    {
        $configurator = new Configurator;

        // set Nette DIR variables to proper directories (otherwise it leads to application-module path)
        $configurator->addParameters([
            'appDir' => APP_ROOT . 'app',
            'wwwDir' => APP_ROOT . 'www',
            'tempRoot' => APP_ROOT . 'temp',
        ]);

        if ($this->environment === 'local') {
            $configurator->setDebugMode(true);
        } else {
            $configurator->setDebugMode(false);
        }

        # terminal
        if (!isset($_SERVER['HTTP_HOST']) && (isset($_SERVER['SHELL']) || isset($_SERVER['TERM']))) {
            $configurator->setDebugMode(true);

            // CLI has no clue about host, but sometimes needs to generate absolute URLs via LinkGenerator.
            // Router usually infers the host from current URL or referer, but there's no such thing here.
            // We need to hint the router the correct host (if available).
            //
            // TODO: In the future releases of Nette it's possible to set this directly as LinkGenerator option:
            //   - https://github.com/nette/application/commit/ef333e63950ceea40def63c2e0f253fc90f19e19
            if ($host = self::env('CRM_HOST')) {
                $configurator->addServices([
                    'http.request' => new Request(new UrlScript($host)),
                ]);
            }
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

    public static function env(string $key, string $default = null): ?string
    {
        return $_ENV[$key] ?? $default;
    }
}
