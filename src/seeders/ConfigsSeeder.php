<?php

namespace Crm\ApplicationModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsSeeder implements ISeeder
{
    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;
    
    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ConfigBuilder $configBuilder
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
    }

    public function seed(OutputInterface $output)
    {
        $category = $this->configCategoriesRepository->loadByName('Všeobecne');
        if (!$category) {
            $category = $this->configCategoriesRepository->add('Všeobecne', 'fa fa-globe', 100);
            $output->writeln('  <comment>* config category <info>Všeobecne</info> created</comment>');
        } else {
            $output->writeln('  * config category <info>Všeobecne</info> exists');
        }

        $name = 'currency';
        $value = 'EUR';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Globalna mena')
                ->setDescription('Globalna mena pouzivana pri vsetkych platbach v ISO-4217 formate')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(110)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'site_title';
        $value = 'CRM';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Meno stránky')
                ->setDescription('Základný názov stránky - to čo sa zobrazí v title stranky')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(100)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'site_description';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Popis stránky')
                ->setDescription('Používa sa v meta dátach stránky.')
                ->setType(ApplicationConfig::TYPE_TEXT)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(200)
                ->save();
            $output->writeln("  * config item <info>$name</info> created");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'site_url';
        $value = 'http://crm.localhost.sk';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Base url stranky')
                ->setDescription('Zakladna url kde bezi crm')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(false)
                ->setConfigCategory($category)
                ->setSorting(250)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'cms_url';
        $value = '/';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('CMS URL')
                ->setDescription('URL smerujuca na titulku CMS')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(255)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'default_route';
        $value = 'Subscriptions:Subscriptions:my';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Defaultná routa')
                ->setDescription('Nette routa, ktorá sa má použiť pri requeste na /')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(false)
                ->setConfigCategory($category)
                ->setSorting(260)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'home_route';
        $value = 'Application:Default:default';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Predvolená stránka')
                ->setDescription('Nette routa, ktorá sa má použiť pri presmerovaní užívateľa po prihlásení, zmene hesla, a pod. Formát: `Application:Default:default`.')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(261)
                ->save();
            $output->writeln("  <comment>* Config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln(" * Config item <info>$name</info> exists");
        }

        $name = 'not_logged_in_route';
        $value = ':Application:Default:Default';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Stránka pre neprihlásených')
                ->setDescription('Nette routa, na ktorú má byť používateľ presmerovaný pri návšteve URL, ktorá je dostupná len pre prihlásených používateľov')
                ->setValue($value)
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(false)
                ->setConfigCategory($category)
                ->setSorting(262)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } elseif ($config->has_default_value && $config->value !== $value) {
            $this->configsRepository->update($config, ['value' => $value, 'has_default_value' => true]);
            $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'layout_name';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Layout stránky')
                ->setDescription('Pozor! Nesprávna hodnota môže znefunkčniť stránku')
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(300)
                ->save();
            $output->writeln("  * config item <info>$name</info> created");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'og_image';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Meta obrazok stránky v predplatnom')
                ->setDescription('Treba zadať plnú cestu http://cesta-ku-obrazky.jpg')
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(400)
                ->save();
            $output->writeln("  * config item <info>$name</info> created");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }

        $name = 'header_block';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Kód v hlavičke')
                ->setDescription('Je možné vložiť ľubovoľný kód, ako napríklad Google analytics alebo ďalšie')
                ->setType(ApplicationConfig::TYPE_HTML)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(500)
                ->save();
            $output->writeln("  * config item <info>$name</info> created");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }
    }
}
