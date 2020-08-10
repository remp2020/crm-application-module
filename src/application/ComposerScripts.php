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
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

        require_once $vendorDir . '/autoload.php';

        if (file_exists($vendorDir . '/../.env')) {
            static::runCommand($event, 'application:install_assets');
        }

        // Running ComposerScripts via Composer (e.g. via post-dump-autoload hook) may pass
        // different parameters to Nette container builder than when it runs in a regular PHP script.
        // Nette container builder may not discover all presenters correctly and container may not be initialized properly (see 'scanComposer' and 'scanDirs' in ApplicationExtension for details).
        // This can cause problems in commands working with presenters - for example, when registering user sources, some presenters could be skipped.
        // Solution: touch a random file (here we've chosen config.neon), so container is forced to reload in a subsequent command/script.
        touch($vendorDir . '/../app/config/config.neon');
    }

    private static function runCommand($event, $commandName)
    {
        $core = new \Crm\ApplicationModule\Core(
            realpath($event->getComposer()->getConfig()->get('vendor-dir') . '/../')
        );
        $container = $core->bootstrap();
        $application = new Application();
        $application->setAutoExit(false);
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
