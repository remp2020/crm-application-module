<?php

namespace Crm\ApplicationModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Repositories\ConfigCategoriesRepository;
use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property ConfigsRepository $configsRepository
 * @property ConfigCategoriesRepository $configCategoriesRepository
 * @property ConfigBuilder $configBuilder
 */
trait ConfigsTrait
{
    private function addConfig(OutputInterface $output, $category, $name, $type, $displayName, $description, $defaultValue, $sorting, array $options = null)
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
                ->setOptions($options)
                ->save();

            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");

            if ($config->has_default_value && $config->value !== $defaultValue) {
                $this->configsRepository->update($config, ['value' => $defaultValue, 'has_default_value' => true]);
                $output->writeln('    * default value updated');
            }

            if ($config->config_category_id !== $category->id) {
                $this->configsRepository->update($config, [
                    'config_category_id' => $category->id,
                ]);
                $output->writeln('    * category updated');
            }

            if ($config->sorting !== $sorting) {
                $this->configsRepository->update($config, [
                    'sorting' => $sorting,
                ]);
                $output->writeln('    * sorting updated');
            }

            if ($config->display_name !== $displayName) {
                $this->configsRepository->update($config, [
                    'display_name' => $displayName,
                ]);
                $output->writeln('    * display name updated');
            }

            if ($config->description !== $description) {
                $this->configsRepository->update($config, [
                    'description' => $description,
                ]);
                $output->writeln('    * description updated');
            }
        }
    }

    private function getCategory($output, $name, $icon = null, $sorting = null)
    {
        $category = $this->configCategoriesRepository->loadByName($name);
        if (!$category) {
            if ($icon && $sorting) {
                $category = $this->configCategoriesRepository->add($name, $icon, $sorting);
            } else {
                $category = $this->configCategoriesRepository->add($name);
            }

            $output->writeln('  <comment>* config category <info>' . $name . '</info> created</comment>');
        }

        $update = [];
        if ($icon && $category->icon !== $icon) {
            $update['icon'] = $icon;
        }
        if ($sorting && $category->sorting !== $sorting) {
            $update['sorting'] = $sorting;
        }
        if (count($update) > 0) {
            $this->configCategoriesRepository->update($category, $update);
        }

        return $category;
    }
}
