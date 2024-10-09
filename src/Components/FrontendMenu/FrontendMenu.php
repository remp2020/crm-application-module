<?php

namespace Crm\ApplicationModule\Components\FrontendMenu;

use Crm\ApplicationModule\Models\DataProvider\DataProviderManager;
use Crm\ApplicationModule\Models\DataProvider\FrontendMenuDataProviderInterface;
use Crm\ApplicationModule\Models\Menu\MenuContainerInterface;
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
    private string $filePath = __DIR__ . DIRECTORY_SEPARATOR . 'frontend_menu.latte';

    protected MenuContainerInterface $menuContainer;

    public function __construct(
        private readonly DataProviderManager $dataProviderManager
    ) {
    }

    public function setMenuContainer(MenuContainerInterface $menuContainer): void
    {
        $this->menuContainer = $menuContainer;
    }

    public function setTemplate(string $templateFile): void
    {
        $this->filePath = __DIR__ . DIRECTORY_SEPARATOR . $templateFile;
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->filePath = $templatePath;
    }

    public function render()
    {
        /** @var FrontendMenuDataProviderInterface[] $providers */
        $providers = $this->dataProviderManager->getProviders('frontend_menu', FrontendMenuDataProviderInterface::class);
        foreach ($providers as $provider) {
            $provider->provide(['menuContainer' => $this->menuContainer]);
        }

        $this->template->menuItems = $this->menuContainer->getMenuItems();
        $this->template->setFile($this->filePath);
        $this->template->render();
    }
}
