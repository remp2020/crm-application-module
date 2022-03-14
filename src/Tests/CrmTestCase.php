<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\ResettableInterface;
use Nette\DI\Container;
use Nette\Http\Session;
use PHPUnit\Framework\TestCase;

/**
 * Class DatabaseTestCase
 * Each test truncates all repositories specified in requiredRepositories method, so DB is always in clean state
 * @package Tests
 */
abstract class CrmTestCase extends TestCase
{
    /** @var Container */
    protected $container;

    protected function setUp(): void
    {
        $_POST = [];
        $_GET = [];
        $_SESSION = [];

        $this->container = $GLOBALS['container'];

        foreach ($this->container->findByType(ResettableInterface::class) as $serviceName) {
            /** @var ResettableInterface $resettable */
            $resettable = $this->container->getService($serviceName);
            $resettable->reset();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $session = $this->container->getByType(Session::class);
        $session->close();
    }

    protected function inject($className)
    {
        return $this->container->getByType($className);
    }
}
