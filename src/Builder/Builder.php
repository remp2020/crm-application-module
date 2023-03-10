<?php

namespace Crm\ApplicationModule\Builder;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

abstract class Builder
{
    private array $data;
    private array $options;
    private array $errors;

    protected $tableName = 'undefined';

    /** @return bool */
    abstract public function isValid();

    /** @return ActiveRow|bool */
    public function save()
    {
        if ($this->isValid()) {
            return $this->store($this->tableName);
        } else {
            return false;
        }
    }

    public function __construct(protected Explorer $database)
    {
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
     * @return $this
     */
    protected function addError(string $error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function get(string $key)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }
        return null;
    }

    protected function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    protected function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @return bool|int|ActiveRow
     */
    protected function store(string $tableName)
    {
        return $this->database->table($tableName)->insert($this->getData());
    }
}
