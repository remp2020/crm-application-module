<?php

namespace Crm\ApplicationModule\Repository;

use Nette\Utils\DateTime;

trait RetentionData
{
    protected function removingField()
    {
        return 'created_at';
    }

    public function removeOldData($from = '-2 months')
    {
        return $this->getTable()->where([$this->removingField() . ' < ?' => DateTime::from($from)])->delete();
    }
}
