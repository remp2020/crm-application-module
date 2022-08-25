<?php

namespace Crm\ApplicationModule\DI;

use Contributte\Translation\DI\TranslationProviderInterface;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;

final class ApplicationModuleExtension extends CompilerExtension implements TranslationProviderInterface
{
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        // set extension parameters for use in config; forcing array as DI Helper doesn't support objects
        $builder->parameters['redis_client_factory'] = (array) $this->config->redis_client_factory;

        // load services from config and register them to Nette\DI Container
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );

        if (count($this->config->redis_client_factory->replication->sentinels)) {
            $builder->getDefinition('redisClientFactory')
                ->addSetup('configureSentinel', [
                    $this->config->redis_client_factory->replication->service,
                    $this->config->redis_client_factory->replication->sentinels,
                ]);
        }
    }

    public function getConfigSchema(): Nette\Schema\Schema
    {
        $sentinelConfig = Expect::structure([
            'scheme' => Expect::string()->dynamic(),
            'host' => Expect::string()->dynamic(),
            'port' => Expect::int()->dynamic(),
        ])->castTo('array');

        return Expect::structure([
            'redis_client_factory' => Expect::structure([
                'prefix' => Expect::string()->dynamic(),
                'replication' => Expect::structure([
                    'service' => Expect::string()->dynamic(),
                    'sentinels' => Expect::arrayOf($sentinelConfig)->dynamic()
                ])
            ])
        ]);
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        // load presenters from extension to Nette
        $builder->getDefinition($builder->getByType(\Nette\Application\IPresenterFactory::class))
            ->addSetup('setMapping', [['Application' => 'Crm\ApplicationModule\Presenters\*Presenter']]);

        $multiplier = new \Contributte\FormMultiplier\DI\MultiplierExtension();
        $multiplier->setConfig((object) [
            'name' => 'addMultiplier',
        ]);
        $this->compiler->addExtension('multiplierExtension', $multiplier);
    }

    /**
     * Return array of directories, that contain resources for translator.
     * @return string[]
     */
    public function getTranslationResources(): array
    {
        return [__DIR__ . '/../lang/'];
    }
}
