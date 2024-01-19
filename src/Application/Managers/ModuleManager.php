<?php

namespace Crm\ApplicationModule\Application\Managers;

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
        $className = get_class($applicationModule);
        $moduleNamespace = substr(
            $className,
            0,
            strrpos(get_class($applicationModule), '\\')
        );
        if (!Strings::endsWith($moduleNamespace, 'Module')) {
            throw new ApplicationException(
                "CRM module name has to belong to namespace with naming pattern '*Module', used namespace '{$moduleNamespace}'"
            );
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
