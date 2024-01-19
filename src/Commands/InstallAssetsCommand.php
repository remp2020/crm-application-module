<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\Managers\AssetsManager;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallAssetsCommand extends Command
{
    private $assetsManager;

    public function __construct(
        AssetsManager $assetsManager
    ) {
        parent::__construct();
        $this->assetsManager = $assetsManager;
    }

    protected function configure()
    {
        $this->setName('application:install_assets')
            ->setDescription('Installs assets registered by modules');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->assetsManager->getCopyIntents() as $copyIntent) {
            [$sourceDirectory, $destinationDirectory] = $copyIntent;
            $output->writeln("Copying assets from <info>{$sourceDirectory}</info> to <info>{$destinationDirectory}</info>");
            FileSystem::copy($sourceDirectory, $destinationDirectory);
        }

        return 0;
    }
}
