<?php

namespace Crm\ApplicationModule\Models\Database;

use Nette\Utils\DateTime;

trait RetentionData
{
    private $retentionThreshold = '-2 months';

    private $retentionRemovingField = 'created_at';

    private $retentionForever = false;

    public function getRetentionRemovingField(): string
    {
        return $this->retentionRemovingField;
    }

    public function setRetentionThreshold(string $threshold, string $removingField = null): void
    {
        $this->retentionThreshold = $threshold;

        if ($removingField !== null) {
            $this->retentionRemovingField = $removingField;
        }
    }

    public function setRetentionForever(): void
    {
        $this->retentionForever = true;
    }

    public function getRetentionThreshold(): string
    {
        return $this->retentionThreshold;
    }

    public function removeOldData()
    {
        if (!$this->retentionForever) {
            return $this->getTable()->where([
                $this->getRetentionRemovingField() . ' < ?' => DateTime::from($this->retentionThreshold),
            ])->delete();
        }

        return null;
    }
}
