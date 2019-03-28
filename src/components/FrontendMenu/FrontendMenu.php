<?php

namespace Crm\ApplicationModule\Components;

use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Nette\Application\UI;

class FrontendMenu extends UI\Control
{
    private $templateName = 'frontend_menu.latte';

    /** @var MenuContainerInterface */
    private $menuItems;

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
        $this->template->menuItems = $this->menuItems->getMenuItems();
        $this->template->setFile(__DIR__ . '/' . $this->templateName);
        $this->template->render();
    }
}
