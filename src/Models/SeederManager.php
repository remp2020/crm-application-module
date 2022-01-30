<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Seeders\ISeeder;

class SeederManager
{
    private $seeders = [];

    /**
     * Higher priority means that seeder is going to be executed after seeders
     * with lower priority so that in case of conflict the seeder with higher priority wins.
     *
     * @param ISeeder $seeder
     * @param int $priority
     */
    public function addSeeder(ISeeder $seeder, int $priority = 100)
    {
        $this->seeders[$priority][] = $seeder;
    }

    public function removeSeeders()
    {
        $this->seeders = [];
    }

    /**
     * @return ISeeder[]
     */
    public function getSeeders(): array
    {
        $sortedSeeders = [];

        ksort($this->seeders);
        foreach ($this->seeders as $prioritySeeders) {
            array_push($sortedSeeders, ...$prioritySeeders);
        }

        return $sortedSeeders;
    }
}
