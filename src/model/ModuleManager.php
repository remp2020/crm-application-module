<?php

namespace Crm\ApplicationModule;

class ModuleManager
{
    /** @var ApplicationModuleInterface[] */
    private $modules = [];

    /**
     * @param ApplicationModuleInterface $applicationModule
     * @param int $order Order in which should be modules stored.
     */
    public function addModule(ApplicationModuleInterface $applicationModule, $order = 1000)
    {
        if (isset($this->modules[$order])) {
            do {
                $order++;
            } while (isset($this->modules[$order]));
        }
        $this->modules[$order] = $applicationModule;
    }

    public function removeModule(ApplicationModuleInterface $applicationModule)
    {
        $class = get_class($applicationModule);
        foreach ($this->modules as $order => $module) {
            if ($module instanceof $class) {
                unset($this->modules[$order]);
            }
        }
    }

    public function removeModules()
    {
        $this->modules = [];
    }

    /**
     * @return ApplicationModuleInterface[]
     */
    public function getModules()
    {
        ksort($this->modules, SORT_NUMERIC);
        return $this->modules;
    }
}
