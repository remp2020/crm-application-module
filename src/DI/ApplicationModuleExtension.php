<?php

namespace Crm\ApplicationModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

final class ApplicationModuleExtension extends CompilerExtension implements ITranslationProvider
{
    private $defaults = [
        'redis_client_factory' => [
            'prefix' => null,
        ],
    ];

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        // load config and preset defaults if value is missing
        $this->config = $this->validateConfig($this->defaults);

        // set extension parameters for use in config
        $builder->parameters['redis_client_factory'] = $this->config['redis_client_factory'];

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
