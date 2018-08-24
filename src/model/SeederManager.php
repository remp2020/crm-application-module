<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Seeders\ISeeder;

class SeederManager
{
    private $seeders = [];

    public function addSeeder(ISeeder $seeder)
    {
        $this->seeders[] = $seeder;
    }

    public function removeSeeders()
    {
        $this->seeders = [];
    }

    /**
     * @return ISeeder[]
     */
    public function getSeeders()
    {
        return $this->seeders;
    }
}
