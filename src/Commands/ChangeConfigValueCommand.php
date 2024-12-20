<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Commands;

use Composer\Console\Input\InputArgument;
use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeConfigValueCommand extends Command
{
    use DecoratedCommandTrait;

    public function __construct(
        private readonly ConfigsRepository $configsRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('application:change_config_value')
            ->setDescription('Changes VAT to selected subscription type items and related payments.')
            ->addArgument(
                'config_name',
                InputArgument::REQUIRED,
                "Name of the config to change.",
            )
            ->addArgument(
                'config_value',
                InputArgument::REQUIRED,
                "Value to be applied.",
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configName = $input->getArgument('config_name');

        $config = $this->configsRepository->loadByName($configName);
        if (!$config) {
            $output->writeln("<error>ERR:</error> Config doesn't exist: " . $configName);
            return Command::FAILURE;
        }

        $value = $input->getArgument('config_value');
        if ($config->value === $value) {
            $output->writeln("Config <info>{$configName}</info> already set to <info>{$value}</info>.");
            return Command::SUCCESS;
        }

        $output->write("Changing <info>{$configName}</info> from <comment>{$config->value}</comment> to <comment>{$value}</comment>: ");
        $this->configsRepository->update($config, ['value' => $value]);
        $output->writeln('OK');

        return Command::SUCCESS;
    }
}
