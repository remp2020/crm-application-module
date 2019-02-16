<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCommand extends Command
{
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager
    ) {
        parent::__construct();
        $this->moduleManager = $moduleManager;
    }

    protected function configure()
    {
        $this->setName('application:cache')
            ->setDescription('Resets application cache (per-module)')
            ->addOption(
                'tags',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Tag specifies which group of cache values should be reset.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tags = $input->getOption('tags');

        foreach ($this->moduleManager->getModules() as $module) {
            $className = get_class($module);
            $output->writeln("Caching module <info>{$className}</info>");
            $module->cache($output, $tags);
        }
    }
}
