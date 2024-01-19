<?php

namespace Crm\ApplicationModule\Models\Config;

use Nette\DI\CompilerExtension;

class ConfigExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $config = $this->getConfig();

        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('config_overrider'))
            ->setType('Crm\ApplicationModule\Models\Config\LocalConfig')
            ->setArguments([$config])
            ->setAutowired(true);
    }
}
