<?php

namespace Crm\ApplicationModule\Repositories;

use Crm\ApplicationModule\Events\ConfigUpdatedEvent;
use Crm\ApplicationModule\Models\Database\Repository;
use Crm\ApplicationModule\Models\Database\Selection;
use DateTime;
use League\Event\Emitter;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ConfigsRepository extends Repository
{
    protected $tableName = 'configs';

    public function __construct(
        Explorer $database,
        AuditLogRepository $auditLogRepository,
        private Emitter $emitter,
    ) {
        parent::__construct($database);
        $this->auditLogRepository = $auditLogRepository;
    }

    final public function all(): Selection
    {
        return $this->getTable()->order('sorting');
    }

    /**
     * @deprecated `autoload` flag is not used anymore and will be removed in the next future releases. Use `all()` instead.
     */
    final public function loadAllAutoload()
    {
        return $this->getTable()->where('autoload', true)->order('sorting');
    }

    final public function loadByName($name)
    {
        return $this->getTable()->where('name', $name)->fetch();
    }

    final public function loadByCategory(ActiveRow $configCategory)
    {
        return $this->loadByCategoryId($configCategory->id);
    }

    final public function loadByCategoryId($configCategoryId)
    {
        return $this->getTable()->where('config_category_id', $configCategoryId)->order('sorting');
    }

    final public function update(ActiveRow &$row, $data)
    {
        $originalValue = $row->value;

        $data['updated_at'] = new DateTime();
        if (!isset($data['has_default_value'])) {
            $data['has_default_value'] = false;
        }

        $result = parent::update($row, $data);
        if (isset($data['value']) && $originalValue !== $data['value']) {
            $this->emitter->emit(new ConfigUpdatedEvent($row, $originalValue));
        }

        return $result;
    }
}
