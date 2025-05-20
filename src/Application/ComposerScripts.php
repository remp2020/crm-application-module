<?php

namespace Crm\ApplicationModule\Application;

use Composer\Composer;
use Composer\Factory;
use Composer\Script\Event;
use Composer\SelfUpdate\Versions;

class ComposerScripts
{
    public static function postAutoloadDump(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

        if (file_exists($vendorDir . '/../.env')) {
            $resultCode = 0;
            $output = [];

            /** @phpstan-ignore-next-line */
            exec('php bin/command.php application:install_assets', $output, $resultCode); // @phpstan-ignore-line
            if ($resultCode) {
                $event->getIO()->write("<warning> CRM </warning> Unable to run <comment>application:install_assets</comment> command, please run <comment>php bin/command.php phinx:migrate</comment> command first.");
            } else {
                $event->getIO()->write(implode("\n", $output));
            }
        }
    }

    public static function checkVersion(Event $event): void
    {
        $currentVersion = Composer::getVersion();

        if (version_compare($currentVersion, '2.0.0', '<')) {
            $event->getIO()->write(sprintf(
                'You are using old Composer version (%s), 2.x is required. Please run <comment>composer self-update --2</comment> first.',
                $currentVersion,
            ));
            exit(1);
        }

        $versionsUtil = new Versions(
            $event->getComposer()->getConfig(),
            Factory::createHttpDownloader($event->getIO(), $event->getComposer()->getConfig()),
        );
        $latestVersion = $versionsUtil->getLatest()['version'];

        if (version_compare($currentVersion, $latestVersion, '<')) {
            $event->getIO()->write(sprintf(
                'Your Composer version (%s) is too old, %s is required. Please run <comment>composer self-update</comment> first.',
                $currentVersion,
                $latestVersion,
            ));
            exit(1);
        }
    }
}
