<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Seeders\ISeeder;
use Nette\DI\Container;
use Nette\Database\Context;
use PDOException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseTestCase
 * Each test truncates all repositories specified in requiredRepositories method, so DB is always in clean state
 * @package Tests
 */
abstract class DatabaseTestCase extends TestCase
{
    use RefreshContainerTrait;

    /** @var Container */
    protected $container;

    /** @var  Context */
    protected $database;

    protected $repositories = [];

    abstract protected function requiredRepositories(): array;

    abstract protected function requiredSeeders(): array;

    protected function getRepository(string $class)
    {
        if (array_key_exists($class, $this->repositories)) {
            return $this->repositories[$class];
        }
        throw new \Exception("Repository $class missing in required repositories");
    }

    protected function setUp(): void
    {
        $_POST = [];
        $_GET = [];

        $this->container = $GLOBALS['container'];
        $this->database = $this->inject(Context::class);

        foreach ($this->requiredRepositories() as $repositoryClass) {
            $this->repositories[$repositoryClass] = $this->inject($repositoryClass);
        }
        /** @var  $seederClass */
        foreach ($this->requiredSeeders() as $seederClass) {
            /** @var ISeeder $seeder */
            $seeder = $this->inject($seederClass);
            $seeder->seed($this->inject(OutputInterface::class));
        }
    }

    protected function tearDown(): void
    {
        $truncateTables = implode(' ', array_map(function ($repo) {
            $property = (new \ReflectionClass($repo))->getProperty('tableName');
            $property->setAccessible(true);
            return "TRUNCATE `{$property->getValue($repo)}`;";
        }, array_values($this->repositories)));

        $db = $this->database->getConnection()->getPdo();
        $sql = "
SET FOREIGN_KEY_CHECKS=0;
{$truncateTables}
SET FOREIGN_KEY_CHECKS=1;
";

        try {
            $db->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    protected function inject($className)
    {
        return $this->container->getByType($className);
    }
}
