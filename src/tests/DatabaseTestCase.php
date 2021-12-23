<?php

namespace Crm\ApplicationModule\Tests;

use Crm\ApplicationModule\Seeders\ISeeder;
use Nette\Database\Context;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseTestCase
 * Each test truncates all repositories specified in requiredRepositories method, so DB is always in clean state
 * @package Tests
 */
abstract class DatabaseTestCase extends CrmTestCase
{
    use RefreshContainerTrait;

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
        parent::setUp();

        $this->database = $this->inject(Context::class);

        foreach ($this->requiredRepositories() as $repositoryClass) {
            $this->repositories[$repositoryClass] = $this->inject($repositoryClass);
        }

        $truncateTables = implode(' ', array_map(function ($repo) {
            $property = (new \ReflectionClass($repo))->getProperty('tableName');
            $property->setAccessible(true);
            return "DELETE FROM `{$property->getValue($repo)}`;";
        }, array_values($this->repositories)));

        $db = $this->database->getConnection()->getPdo();
        $sql = "
SET FOREIGN_KEY_CHECKS=0;
{$truncateTables}
SET FOREIGN_KEY_CHECKS=1;
";
        $db->exec($sql);

        /** @var  $seederClass */
        foreach ($this->requiredSeeders() as $seederClass) {
            /** @var ISeeder $seeder */
            $seeder = $this->inject($seederClass);
            $seeder->seed($this->inject(OutputInterface::class));
        }
    }
}
