<?php

namespace Crm\ApplicationModule\Menu;

interface MenuContainerInterface
{
    public function attachMenuItem(MenuItemInterface $menuItem);

    /**
     * @return MenuItemInterface[]
     */
    public function getMenuItems();

    public function getMenuItemByLink(string $link): ?MenuItemInterface;

    public function isEmpty();

    public function attachMenuItemToForeignModule(
        string $foreignMenuLink,
        MenuItem $internalMenuItem,
        MenuItem $menuItem
    );

    public function removeMenuItemByLink(string $menuItemLink);
}
