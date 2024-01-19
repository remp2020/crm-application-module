<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Models\Database\Repository;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

class SnippetsRepository extends Repository
{
    protected $tableName = 'snippets';

    final public function all()
    {
        return $this->getTable()->order('sorting');
    }

    final public function add($identifier, $title, $html, $sorting = 100, $isActive = true, $hasDefaultValue = false)
    {
        return $this->insert([
            'title' => $title,
            'identifier' => $identifier,
            'sorting' => $sorting,
            'html' => $html,
            'is_active' => $isActive,
            'has_default_value' => $hasDefaultValue,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
        ]);
    }

    final public function update(ActiveRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        if (!isset($data['has_default_value'])) {
            $data['has_default_value'] = false;
        }
        return parent::update($row, $data);
    }

    final public function exists($identifier)
    {
        return $this->getTable()->where(['identifier' => $identifier])->count('*') > 0;
    }

    final public function loadAllByIdentifier($identifier)
    {
        return $this->getTable()->where('identifier', $identifier)->fetchAll();
    }

    final public function loadAll()
    {
        return $this->getTable()->order('title ASC');
    }

    final public function markUsed(ActiveRow $snippet)
    {
        return parent::update($snippet, [
            'last_used' => new DateTime(),
            'total_used+=' => 1,
        ]);
    }
}
