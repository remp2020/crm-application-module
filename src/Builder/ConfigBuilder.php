<?php

namespace Crm\ApplicationModule\Builder;

use Crm\ApplicationModule\Events\ConfigCreatedEvent;
use DateTime;
use League\Event\Emitter;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class ConfigBuilder extends Builder
{
    protected $tableName = 'configs';

    public function __construct(Explorer $database, private Emitter $emitter)
    {
        parent::__construct($database);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (!$this->get('name')) {
            $this->addError('Nebolo zadané meno');
        }
        if (!$this->get('type')) {
            $this->addError('Nebolo zadaný typ');
        }

        if (!$this->get('config_category_id')) {
            $this->addError('Nebola zadaná kategória');
        }

        if (count($this->getErrors()) > 0) {
            return false;
        }
        return true;
    }

    protected function setDefaults()
    {
        $this->set('created_at', new DateTime());
        $this->set('updated_at', new DateTime());
        $this->set('value', '');
        $this->set('has_default_value', true);
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->set('name', $name);
    }

    /**
     * @param $displayName
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        return $this->set('display_name', $displayName);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->set('value', $value);
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->set('description', $description);
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        return $this->set('type', $type);
    }

    /**
     * @param $sorting
     * @return $this
     */
    public function setSorting($sorting)
    {
        return $this->set('sorting', $sorting);
    }

    /**
     * Set autoload
     *
     * If true, config is autoloaded to cache.
     *
     * @param $autoload
     * @return $this
     */
    public function setAutoload($autoload)
    {
        return $this->set('autoload', $autoload);
    }

    /**
     * Set config value options
     *
     * @throws JsonException
     */
    public function setOptions(?array $options): self
    {
        if (!empty($options)) {
            $this->set('options', Json::encode($options));
        }
        return $this;
    }

    /**
     * @param $locked
     * @return $this
     */
    public function setLocked($locked)
    {
        return $this->set('locked', $locked);
    }

    /**
     * @param ActiveRow $category
     * @return $this
     */
    public function setConfigCategory(ActiveRow $category)
    {
        return $this->set('config_category_id', $category->id);
    }

    protected function store($tableName)
    {
        $row = parent::store($tableName);
        if ($row) {
            $this->emitter->emit(new ConfigCreatedEvent($row));
        }
        return $row;
    }
}
