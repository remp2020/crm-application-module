<?php

namespace Crm\ApplicationModule;

use Nette\Application\ApplicationException;
use Nette\Utils\Strings;

class ModuleManager
{
    /** @var ApplicationModuleInterface[] */
    private $modules = [];

    /**
     * @param ApplicationModuleInterface $applicationModule
     * @param int $order Order in which should be modules stored.
     * @throws ApplicationException
     */
    public function addModule(ApplicationModuleInterface $applicationModule, $order = 1000)
    {
        $moduleName = explode('\\', get_class($applicationModule))[1];
        if (!Strings::endsWith($moduleName, 'Module')) {
            throw new ApplicationException("Application module name has to follow naming pattern: '*Module', used name '{$moduleName}'");
        }
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
