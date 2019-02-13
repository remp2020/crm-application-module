<?php

namespace Crm\ApplicationModule\Snippet\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class SnippetsRepository extends Repository
{
    protected $tableName = 'snippets';

    public function all()
    {
        return $this->getTable()->order('sorting');
    }

    public function add($identifier, $title, $html, $sorting = 100, $isActive = true, $hasDefaultValue = false)
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

    public function update(IRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        if (!isset($data['has_default_value'])) {
            $data['has_default_value'] = false;
        }
        return parent::update($row, $data);
    }

    public function exists($identifier)
    {
        return $this->getTable()->where(['identifier' => $identifier])->count('*') > 0;
    }

    public function loadAllByIdentifier($identifier)
    {
        return $this->getTable()->where('identifier', $identifier)->fetchAll();
    }

    public function loadAll()
    {
        return $this->getTable()->order('title ASC');
    }

    public function markUsed(IRow $snippet)
    {
        return parent::update($snippet, [
            'last_used' => new DateTime(),
            'total_used+=' => 1,
        ]);
    }
}
