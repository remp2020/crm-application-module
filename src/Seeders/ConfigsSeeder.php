<?php

namespace Crm\ApplicationModule\Seeders;

use Crm\ApplicationModule\Application\Core;
use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\ApplicationModule\Repositories\ConfigCategoriesRepository;
use Crm\ApplicationModule\Repositories\ConfigsRepository;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsSeeder implements ISeeder
{
    use ConfigsTrait;

    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;

    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ConfigBuilder $configBuilder,
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
    }

    public function seed(OutputInterface $output)
    {
        $categoryName = 'application.config.category';
        $category = $this->configCategoriesRepository->loadByName($categoryName);
        if (!$category) {
            $category = $this->configCategoriesRepository->add($categoryName, 'fa fa-globe', 100);
            $output->writeln('  <comment>* config category <info>Všeobecne</info> created</comment>');
        } else {
            $output->writeln('  * config category <info>Všeobecne</info> exists');
        }

        $this->addConfig(
            $output,
            $category,
            'site_title',
            ApplicationConfig::TYPE_STRING,
            'application.config.site_title.name',
            'application.config.site_title.description',
            'CRM',
            100,
        );

        $this->addConfig(
            $output,
            $category,
            'site_description',
            ApplicationConfig::TYPE_STRING,
            'application.config.site_description.name',
            'application.config.site_description.description',
            null,
            110,
        );


        $this->addConfig(
            $output,
            $category,
            'site_url',
            ApplicationConfig::TYPE_STRING,
            'application.config.site_url.name',
            'application.config.site_url.description',
            Core::env('CRM_HOST'),
            110,
        );

        $this->addConfig(
            $output,
            $category,
            'currency',
            ApplicationConfig::TYPE_STRING,
            'application.config.currency.name',
            'application.config.currency.description',
            'EUR',
            130,
        );

        $this->addConfig(
            $output,
            $category,
            'cms_url',
            ApplicationConfig::TYPE_STRING,
            'application.config.cms_url.name',
            'application.config.cms_url.description',
            '/',
            200,
        );

        $this->addConfig(
            $output,
            $category,
            'contact_email',
            ApplicationConfig::TYPE_STRING,
            'application.config.contact_email.name',
            'application.config.contact_email.description',
            'info@crm.press',
            210,
        );

        $this->addConfig(
            $output,
            $category,
            'default_route',
            ApplicationConfig::TYPE_STRING,
            'application.config.default_route.name',
            'application.config.default_route.description',
            'Subscriptions:Subscriptions:my',
            260,
        );

        $this->addConfig(
            $output,
            $category,
            'home_route',
            ApplicationConfig::TYPE_STRING,
            'application.config.home_route.name',
            'application.config.home_route.description',
            'Application:Default:default',
            270,
        );

        $this->addConfig(
            $output,
            $category,
            'not_logged_in_route',
            ApplicationConfig::TYPE_STRING,
            'application.config.not_logged_in_route.name',
            'application.config.not_logged_in_route.description',
            ':Application:Default:Default',
            280,
        );

        $this->addConfig(
            $output,
            $category,
            'layout_name',
            ApplicationConfig::TYPE_STRING,
            'application.config.layout_name.name',
            'application.config.layout_name.description',
            null,
            300,
        );

        $this->addConfig(
            $output,
            $category,
            'og_image',
            ApplicationConfig::TYPE_STRING,
            'application.config.og_image.name',
            'application.config.og_image.description',
            null,
            310,
        );

        $this->addConfig(
            $output,
            $category,
            'header_block',
            ApplicationConfig::TYPE_HTML,
            'application.config.header_block.name',
            'application.config.header_block.description',
            null,
            500,
        );

        $this->addConfig(
            $output,
            $category,
            'admin_logo',
            ApplicationConfig::TYPE_STRING,
            'application.config.admin_logo.name',
            'application.config.admin_logo.description',
            null,
            500,
        );

        $this->addConfig(
            $output,
            $category,
            'localized_countries',
            ApplicationConfig::TYPE_BOOLEAN,
            'application.config.localized_countries.name',
            'application.config.localized_countries.description',
            false,
            510,
        );

        // empty categories cleanup
        $emptyCategories = $this->configCategoriesRepository->getTable()
            ->where(':configs.id IS NULL');

        foreach ($emptyCategories as $category) {
            $this->configCategoriesRepository->delete($category);
        }
    }
}
