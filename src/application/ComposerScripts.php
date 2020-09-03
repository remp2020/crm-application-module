<?php

namespace Crm\ApplicationModule;

use Composer\Script\Event;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
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
            try {
                static::runCommand($event, 'application:install_assets');
            } catch (CommandNotFoundException $commandNotFoundException) {
                $event->getIO()->write("<warning> CRM </warning> Unable to run <comment>application:install_assets</comment> command, please run <comment>php bin/command.php phinx:migrate</comment> command first.");
            }
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

        /** @var ApplicationManager $applicationManager */
        $applicationManager = $container->getByType(\Crm\ApplicationModule\ApplicationManager::class);
        $commands = $applicationManager->getCommands();
        foreach ($commands as $command) {
            $application->add($command);
        }

        $application->run(new ArrayInput(['command' => $commandName]));
    }
}
