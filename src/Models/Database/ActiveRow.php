<?php

namespace Crm\ApplicationModule\Models\Database;

class ActiveRow extends \Nette\Database\Table\ActiveRow
{
    public function delete(): int
    {
        throw new \Exception('Direct delete is not allowed, use repository\'s delete');
    }

    public function update(iterable $data): bool
    {
        throw new \Exception('Direct update is not allowed, use repository\'s update');
    }
}
