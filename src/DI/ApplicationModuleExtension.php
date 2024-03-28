<?php

namespace Crm\ApplicationModule\DI;

use Contributte\FormMultiplier\DI\MultiplierExtension;
use Contributte\Translation\DI\TranslationProviderInterface;
use Nette\Application\IPresenterFactory;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ApplicationModuleExtension extends CompilerExtension implements TranslationProviderInterface
{
    public function setCompiler(Compiler $compiler, string $name): static
    {
        $multiplier = new MultiplierExtension();
        $multiplier->setConfig((object) [
            'name' => 'addMultiplier',
        ]);
        $compiler->addExtension('multiplier', $multiplier);

        return parent::setCompiler($compiler, $name);
    }

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

    public function getConfigSchema(): Schema
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
        $builder->getDefinition($builder->getByType(IPresenterFactory::class))
            ->addSetup('setMapping', [['Application' => 'Crm\ApplicationModule\Presenters\*Presenter']]);
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
