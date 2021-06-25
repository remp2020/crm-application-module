<?php

namespace Crm\ApplicationModule;

use Crm\ApplicationModule\Models\Repository\SlugColumnTrait;
use Crm\ApplicationModule\Repository\AuditLogRepository;
use Crm\ApplicationModule\Repository\ReplicaTrait;
use Nette\Caching\IStorage;
use Nette\Database\Context;
use Nette\Database\Table\IRow;

class Repository
{
    use DateFieldsProcessorTrait;
    use SlugColumnTrait;
    use ReplicaTrait;

    /** @var Context */
    protected $database;

    /** @var AuditLogRepository */
    protected $auditLogRepository;

    /** @var string */
    protected $tableName = 'undefined';

    /** @var IStorage */
    protected $cacheStorage;

    /** @var array */
    protected $auditLogExcluded = [];

    public function __construct(
        Context $database,
        IStorage $cacheStorage = null
    ) {
        $this->database = $database;
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * @return Selection
     */
    public function getTable()
    {
        $database = $this->getDatabase(true);
        $selection = new Selection(
            $database,
            $database->getConventions(),
            $this->tableName,
            $this->cacheStorage
        );
        $selection->setReplicaManager($this->getReplicaManager());
        return $selection;
    }

    /**
     * @return \Nette\Database\Table\ActiveRow
     */
    public function find($id)
    {
        return $this->getTable()->where(['id' => $id])->fetch();
    }

    /**
     * @return \Nette\Database\Table\ActiveRow
     */
    public function findBy($column, $value)
    {
        return $this->getTable()->where([$column => $value])->fetch();
    }

    public function totalCount()
    {
        return $this->getTable()->count('*');
    }

    /**
     * Update updates provided record with given $data array and mutates the provided instance. Operation is logged
     * to audit log.
     *
     * @param IRow $row
     * @param array $data values to update
     * @return bool
     *
     * @throws \Exception
     */
    public function update(IRow &$row, $data)
    {
        // require non-replicated database connection for updates and subsequent queries
        $this->getReplicaManager()->setWriteFlag();

        $this->assertSlugs((array) $data);
        $data = $this->processDateFields($data);

        $oldValues = [];
        if ($row instanceof ActiveRow) {
            $oldValues = $row->toArray();
        }

        $res = $this->getTable()->wherePrimary($row->getPrimary())->update($data);
        if (!$res) {
            // if MySQL is set to return number of updated rows (default) instead of number of matched rows,
            // we're halting the execution here and nothing is saved to the audit log.
            return false;
        }

        if ($this->auditLogRepository) {
            // filter internal columns
            $data = $this->filterValues($this->excludeColumns((array)$data));

            // filter unchanged columns
            if (!empty($oldValues)) {
                $oldValues = $this->filterValues($this->excludeColumns($oldValues));

                $oldValues = array_intersect_key($oldValues, (array)$data);
                $data = array_diff_assoc((array)$data, $oldValues); // get changed values
                $oldValues = array_intersect_key($oldValues, (array)$data); // get rid of unchanged $oldValues
            }

            $data = [
                'version' => '1',
                'from' => $oldValues,
                'to' => $data,
            ];
            $this->pushAuditLog(AuditLogRepository::OPERATION_UPDATE, $row->getSignature(), $data);
        }

        $row = $this->getTable()->wherePrimary($row->getPrimary())->fetch();
        return true;
    }

    /**
     * Delete deletes provided record from repository and mutates the provided instance. Operation is logged to audit log.
     *
     * @param IRow $row
     * @return bool
     */
    public function delete(IRow &$row)
    {
        // require non-replicated database connection for deletes and subsequent queries
        $this->getReplicaManager()->setWriteFlag();

        $oldValues = [];
        if ($row instanceof ActiveRow) {
            $oldValues = $row->toArray();
        }
        $res = $this->getTable()->wherePrimary($row->getPrimary())->delete();

        if (!$res) {
            return false;
        }

        if ($this->auditLogRepository) {
            $from = $this->filterValues($this->excludeColumns($oldValues));

            $data = [
                'version' => '1',
                'from' => $from,
                'to' => [],
            ];
            $this->pushAuditLog(AuditLogRepository::OPERATION_DELETE, $row->getSignature(), $data);
        }

        return true;
    }

    /**
     * Insert inserts data to the repository. If single IRow is returned, it attempts to log audit information.
     *
     * @param $data
     * @return bool|int|IRow
     */
    public function insert($data)
    {
        // require non-replicated database connection for inserts and subsequent queries
        $this->getReplicaManager()->setWriteFlag();

        $this->assertSlugs((array) $data);
        $data = $this->processDateFields($data);

        $row = $this->getTable()->insert($data);
        if (!$row instanceof IRow) {
            return $row;
        }

        if ($this->auditLogRepository) {
            $to = $this->filterValues($this->excludeColumns((array)$data));

            $data = [
                'version' => '1',
                'from' => [],
                'to' => $to,
            ];
            $this->pushAuditLog(AuditLogRepository::OPERATION_CREATE, $row->getSignature(), $data);
        }

        return $row;
    }

    /**
     * excludeColumns unsets columns based on the auditLogRepository's definition and returns filtered array.
     *
     * @param $data
     * @return mixed
     */
    private function excludeColumns($data)
    {
        foreach ($this->auditLogExcluded as $excludedColumn) {
            unset($data[$excludedColumn]);
        }
        return $data;
    }

    /**
     * filterValues removes non-scalar values from the array and formats any DateTime to DB string representation.
     *
     * @param array $values
     * @return array
     */
    private function filterValues(array $values)
    {
        foreach ($values as $i => $field) {
            if (is_bool($field)) {
                $values[$i] = (int)$field;
            } elseif ($field instanceof \DateTime) {
                $values[$i] = $field->format('Y-m-d H:i:s');
            } elseif (!is_scalar($field)) {
                unset($values[$i]);
            }
        }
        return $values;
    }

    private function pushAuditLog($operation, $signature, $data)
    {
        if ($data['version'] == 1) {
            if (empty($data['from']) && empty($data['to'])) {
                return;
            }
        }
        $this->auditLogRepository->add($operation, $this->tableName, $signature, $data);
    }
}
