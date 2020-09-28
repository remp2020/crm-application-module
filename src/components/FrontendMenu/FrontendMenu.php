<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Nette\Application\UI;

/**
 * Basic frontend menu component.
 *
 * This component renders frtonend menu items to simple latte template.
 *
 * @package Crm\ApplicationModule\Components
 */
class FrontendMenu extends UI\Control
{
    private $templateName = 'frontend_menu.latte';

    /** @var MenuContainerInterface */
    private $menuItems;

    public $applicationConfig;

    public function __construct(
        ApplicationConfig $applicationConfig
    ) {
        parent::__construct();
        $this->applicationConfig  = $applicationConfig;
    }

    public function setMenuItems(MenuContainerInterface $menuItems)
    {
        $this->menuItems = $menuItems;
    }

    public function setTemplate(string $templateFile): void
    {
        $this->templateName = $templateFile;
    }

    public function render()
    {
        $this->template->siteUrl = $this->applicationConfig->get('site_url');
        $this->template->menuItems = $this->menuItems->getMenuItems();
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
