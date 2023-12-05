<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Seeders\ISeeder;
use Nette\Database\Explorer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseTestCase
 * Each test truncates all repositories specified in requiredRepositories method, so DB is always in clean state
 * @package Tests
 */
abstract class DatabaseTestCase extends CrmTestCase
{
    use RefreshContainerTrait;

    /** @var Explorer */
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
        parent::setUp();

        $this->database = $this->inject(Explorer::class);

        foreach ($this->requiredRepositories() as $repositoryClass) {
            $this->repositories[$repositoryClass] = $this->inject($repositoryClass);
        }

        $db = $this->database->getConnection()->getPdo();
        $db->exec('SET autocommit=0; START TRANSACTION;');

        foreach ($this->requiredSeeders() as $seederClass) {
            /** @var ISeeder $seeder */
            $seeder = $this->inject($seederClass);
            $seeder->seed($this->inject(OutputInterface::class));
        }
    }

    protected function tearDown(): void
    {
        $this->database = $this->inject(Explorer::class);
        $db = $this->database->getConnection()->getPdo();
        $db->exec('ROLLBACK;');

        parent::tearDown();
    }
}
