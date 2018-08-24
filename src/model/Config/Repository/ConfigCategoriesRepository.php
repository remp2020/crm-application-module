<?php

namespace Crm\ApplicationModule\Config\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;

class ConfigCategoriesRepository extends Repository
{
    protected $tableName = 'config_categories';

    public function all()
    {
        return $this->getTable()->order('sorting');
    }

    public function add($name, $icon = 'fa fa-wrench', $sorting = 10)
    {
        $result = $this->insert([
            'name' => $name,
            'icon' => $icon,
            'sorting' => $sorting,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
        if (is_numeric($result)) {
            return $this->getTable()->where('id', $result)->fetch();
        }
        return $result;
    }

    public function loadByName($name)
    {
        return $this->getTable()->where('name', $name)->fetch();
    }
}
