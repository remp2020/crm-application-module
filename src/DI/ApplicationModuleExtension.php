<?php

namespace Crm\ApplicationModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\CompilerExtension;

final class ApplicationModuleExtension extends CompilerExtension implements ITranslationProvider
{
    private $defaults = [
        'redis_client_factory' => [
            'prefix' => null,
            'replication' => [
                'service' => null,
                'sentinels' => [],
            ],
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
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );

        if (count($this->config['redis_client_factory']['replication']['sentinels'])) {
            $builder->getDefinition('redisClientFactory')
                ->addSetup('configureSentinel', [
                    $this->config['redis_client_factory']['replication']['service'],
                    $this->config['redis_client_factory']['replication']['sentinels'],
                ]);
        }
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        // load presenters from extension to Nette
        $builder->getDefinition($builder->getByType(\Nette\Application\IPresenterFactory::class))
            ->addSetup('setMapping', [['Application' => 'Crm\ApplicationModule\Presenters\*Presenter']]);

        $this->compiler->addExtension('multiplierExtension', new \Contributte\FormMultiplier\DI\MultiplierExtension);
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
