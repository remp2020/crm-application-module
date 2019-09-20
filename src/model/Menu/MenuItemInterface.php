<?php

namespace Crm\ApplicationModule\Menu;

interface MenuItemInterface
{
    public function name();

    public function icon();

    public function position();

    public function internal();

    public function link();

    public function addChild(MenuItemInterface $item);

    /**
     * @return MenuItemInterface[]
     */
    public function subItems();

    public function hasSubItems();
}
