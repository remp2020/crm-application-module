<?php

namespace Crm\ApplicationModule;

class ActiveRow extends \Nette\Database\Table\ActiveRow
{
    public function delete()
    {
        throw new \Exception('Direct delete is not allowed, use repository\'s delete');
    }

    public function update($data)
    {
        throw new \Exception('Direct update is not allowed, use repository\'s update');
    }
}
