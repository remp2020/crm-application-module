<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Database\DatabaseTransaction;
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
    protected Explorer $database;

    /** @var DatabaseTransaction $databaseTransaction */
    private DatabaseTransaction $databaseTransaction;

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
        $this->databaseTransaction = $this->inject(DatabaseTransaction::class);

        foreach ($this->requiredRepositories() as $repositoryClass) {
            $this->repositories[$repositoryClass] = $this->inject($repositoryClass);
        }

        $db = $this->database->getConnection()->getPdo();
        $db->exec('SET autocommit=0;');

        $this->databaseTransaction->start();

        foreach ($this->requiredSeeders() as $seederClass) {
            /** @var ISeeder $seeder */
            $seeder = $this->inject($seederClass);
            $seeder->seed($this->inject(OutputInterface::class));
        }
    }

    protected function tearDown(): void
    {
        $this->databaseTransaction->rollback();

        /**
         * Explicitly disconnect from the database to avoid "Too many connections" error when BaseTestCases
         * asks for refreshContainer() which always creates a new instance of Connection & Explorer which then
         * are creating a new connections to the database.
         */
        $this->database->getConnection()->disconnect();

        parent::tearDown();
    }
}
