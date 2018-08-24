<?php

namespace Crm\ApplicationModule\Menu;

class MenuItem implements MenuItemInterface
{
    private $name;

    private $link;

    private $icon;

    private $position;

    private $internal;

    private $childs;

    private $params;

    public function __construct($name, $link = '', $icon = '', $position = 10, $internal = true, $params = [])
    {
        $this->name = $name;
        $this->link = $link;
        $this->icon = $icon;
        $this->position = $position;
        $this->internal = $internal;
        $this->childs = new MenuContainer();
        $this->params = $params;
    }

    public function name()
    {
        return $this->name;
    }

    public function icon()
    {
        return $this->icon;
    }

    public function position()
    {
        return $this->position;
    }

    public function internal()
    {
        return $this->internal;
    }

    public function link()
    {
        return $this->link;
    }

    public function addChild(MenuItemInterface $item)
    {
        $this->childs->attachMenuItem($item);
    }

    public function subItems()
    {
        return $this->childs->getMenuItems();
    }

    public function hasSubItems()
    {
        return !$this->childs->isEmpty();
    }

    public function getParams()
    {
        return $this->params;
    }
}
