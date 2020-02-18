<?php

namespace Crm\ApplicationModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property ConfigsRepository $configsRepository
 * @property ConfigBuilder $configBuilder
 */
trait ConfigsTrait
{
    private function addConfig(OutputInterface $output, $category, $name, $type, $displayName, $description, $defaultValue, $sorting)
    {
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName($displayName)
                ->setDescription($description)
                ->setValue($defaultValue)
                ->setType($type)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting($sorting)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");

            if ($config->has_default_value && $config->value !== $defaultValue) {
                $this->configsRepository->update($config, ['value' => $defaultValue, 'has_default_value' => true]);
                $output->writeln('    * default value updated');
            }

            if ($config->config_category->id !== $category->id) {
                $this->configsRepository->update($config, [
                    'config_category_id' => $category->id
                ]);
                $output->writeln('    * category updated');
            }

            if ($config->sorting !== $sorting) {
                $this->configsRepository->update($config, [
                    'sorting' => $sorting,
                ]);
                $output->writeln('    * sorting updated');
            }
        }
    }
}
