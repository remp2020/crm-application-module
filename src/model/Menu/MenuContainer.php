<?php

namespace Crm\ApplicationModule\Menu;

class MenuContainer implements MenuContainerInterface
{
    /** @var MenuItemInterface[]  */
    private $menuItems = [];

    private $foreignMenuItems = [];

    public function attachMenuItem(MenuItemInterface $menuItem)
    {
        $this->menuItems[] = $menuItem;
    }

    public function getMenuItems()
    {
        $this->attachForeignMenuItems();
        $items = [];
        foreach ($this->menuItems as $item) {
            $position = $item->position();

            $items[$position] = $item;
        }
        ksort($items);
        return $items;
    }

    public function getMenuItemByLink(string $link): ?MenuItemInterface
    {
        foreach ($this->menuItems as $item) {
            if ($item->link() === $link) {
                return $item;
            }
        }
        return null;
    }

    public function isEmpty()
    {
        return count($this->menuItems) == 0;
    }

    /**
     * Try to attach menu item to:
     * - foreign module menu (if exists, search is done by link)
     * - own module menu (if foreign module menu doesn't exist)
     */
    public function attachMenuItemToForeignModule(
        string $foreignMenuLink,
        MenuItem $internalMenuItem,
        MenuItem $menuItem
    ) {
        $this->foreignMenuItems[] = [
            'foreignMenuLink' => $foreignMenuLink,
            'internalMenuItem' => $internalMenuItem,
            'menuItem' => $menuItem
        ];
    }

    private function attachForeignMenuItems()
    {
        foreach ($this->foreignMenuItems as $item) {
            $foreignMenuItem = $this->getMenuItemByLink($item['foreignMenuLink']);
            if (!is_null($foreignMenuItem)) {
                $foreignMenuItem->addChild($item['menuItem']);
            } else {
                $item['internalMenuItem']->addChild($item['menuItem']);
                $this->attachMenuItem($item['internalMenuItem']);
            }
        }
    }
}
