<?php

namespace Crm\ApplicationModule;

use Composer\Script\Event;
use Nette\Database\DriverException;
use Nette\InvalidArgumentException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class ComposerScripts
{
    /**
     * Handle the post-autoload-dump Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';

        if (file_exists($event->getComposer()->getConfig()->get('vendor-dir') . '/../.env')) {
            static::runCommand($event, 'application:install_assets');
        }
    }

    private static function runCommand($event, $commandName)
    {
        $core = new \Crm\ApplicationModule\Core(
            realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/../')
        );
        $container = $core->bootstrap();
        $application = new Application();
        $application->setCatchExceptions(false);

        try {
            /** @var ApplicationManager $applicationManager */
            $applicationManager = $container->getByType(\Crm\ApplicationModule\ApplicationManager::class);
            $commands = $applicationManager->getCommands();
            foreach ($commands as $command) {
                $application->add($command);
            }
        } catch (DriverException $driverException) {
            echo "INFO: Looks like the new fresh install.\n";
        } catch (InvalidArgumentException $invalidArgument) {
            echo "INFO: Looks like the new fresh install - or wrong configuration - '{$invalidArgument->getMessage()}'.\n";
        }

        $application->run(new ArrayInput(['command' => $commandName]));
    }
}
