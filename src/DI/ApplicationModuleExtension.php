<?php

namespace Crm\ApplicationModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class ApplicationModuleExtension extends CompilerExtension implements ITranslationProvider
{
    public function loadConfiguration()
    {
        // load services from config and register them to Nette\DI Container
        Compiler::loadDefinitions(
            $this->getContainerBuilder(),
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        // load presenters from extension to Nette
        $builder->getDefinition($builder->getByType(\Nette\Application\IPresenterFactory::class))
            ->addSetup('setMapping', [['Application' => 'Crm\ApplicationModule\Presenters\*Presenter']]);

        $this->compiler->addExtension('multiplierExtension', new \WebChemistry\Forms\Controls\DI\MultiplierExtension);
    }

    /**
     * Return array of directories, that contain resources for translator.
     * @return string[]
     */
    public function getTranslationResources()
    {
        return [__DIR__ . '/../lang/'];
    }
}
