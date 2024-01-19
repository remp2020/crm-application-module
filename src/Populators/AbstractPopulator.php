<?php

namespace Crm\ApplicationModule\Populators;

use Crm\ApplicationModule\Commands\PopulatorCommand;
use Faker\Generator;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

abstract class AbstractPopulator
{
    /** @var PopulatorCommand */
    protected $populator;

    /** @var Generator */
    protected $faker;

    /** @var Explorer */
    protected $database;

    /** @var int */
    protected $count;

    /** @var string */
    protected $name;

    /**
     *
     * @param string $name
     * @param int $count
     */
    public function __construct($name, $count = 10)
    {
        $this->name = $name;
        $this->count = $count;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     *
     * @param PopulatorCommand $populator
     */
    public function setPopulator($populator)
    {
        $this->populator = $populator;
    }

    /**
     *
     * @param Explorer $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @param Generator $faker
     */
    public function setFaker(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Returns random record from given table.
     * @param string $tableName
     * @return ActiveRow|false
     */
    protected function getRecord($tableName, $where = null)
    {
        $record = $this->database->table($tableName)->order('RAND()');
        return $where ?
            $record->where($where)->limit(1)->fetch() :
            $record->limit(1)->fetch();
    }

    /**
     * Returns id of random record from given table.
     * @param string $tableName
     * @return int
     */
    protected function getId($tableName, $where = null)
    {
        $record = $this->getRecord($tableName, $where);
        return $record ? $record->id : null;
    }

    abstract public function seed($progressBar);
}
