<?php

namespace Crm\ApplicationModule\Builder;

use Nette\Database\Context;
use Nette\Database\Table\IRow;

abstract class Builder
{
    /** @var  Context; */
    protected $database;

    /** @var  array */
    private $data;

    /** @var  array */
    private $options = [];

    /** @var  array */
    private $errors;

    /** @var string */
    protected $tableName = 'undefined';

    /** @return bool */
    abstract public function isValid();

    /** @return IRow|bool */
    public function save()
    {
        if ($this->isValid()) {
            return $this->store($this->tableName);
        } else {
            return false;
        }
    }

    /**
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
        $this->data = [];
        $this->errors = [];
        $this->options = [];
    }

    /**
     * @return $this
     */
    public function createNew()
    {
        $this->data = [];
        $this->errors = [];
        $this->options = [];
        $this->setDefaults();
        return $this;
    }

    protected function setDefaults()
    {
        // nothing to do
    }

    /**
     * @param string $error
     * @return $this
     */
    protected function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function get($key)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }
        return null;
    }

    protected function getOption($key)
    {
        return $this->options[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $tableName
     * @return bool|int|\Nette\Database\Table\IRow
     */
    protected function store($tableName)
    {
        return $this->database->table($tableName)->insert($this->getData());
    }
}
