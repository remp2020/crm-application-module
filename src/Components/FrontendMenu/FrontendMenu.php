<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\DataProvider\DataProviderManager;
use Crm\ApplicationModule\DataProvider\FrontendMenuDataProviderInterface;
use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Nette\Application\UI\Control;

/**
 * Basic frontend menu component.
 *
 * This component renders frontend menu items to simple latte template.
 *
 * @package Crm\ApplicationModule\Components
 */
class FrontendMenu extends Control
{
    private $templateName = 'frontend_menu.latte';

    /** @var MenuContainerInterface */
    private $menuContainer;

    public $applicationConfig;

    private $dataProviderManager;

    public function __construct(
        ApplicationConfig $applicationConfig,
        DataProviderManager $dataProviderManager
    ) {
        $this->applicationConfig  = $applicationConfig;
        $this->dataProviderManager = $dataProviderManager;
    }

    public function setMenuContainer(MenuContainerInterface $menuContainer)
    {
        $this->menuContainer = $menuContainer;
    }

    public function setTemplate(string $templateFile): void
    {
        $this->templateName = $templateFile;
    }

    public function render()
    {
        /** @var FrontendMenuDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders('frontend_menu', FrontendMenuDataProviderInterface::class);
        foreach ($providers as $provider) {
            $provider->provide(['menuContainer' => $this->menuContainer]);
        }

        $this->template->menuItems = $this->menuContainer->getMenuItems();
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
