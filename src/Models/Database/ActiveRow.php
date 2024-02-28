<?php

namespace Crm\ApplicationModule\Models\Database;

class ActiveRow extends \Nette\Database\Table\ActiveRow implements OriginalDataAwareInterface
{
    private array $originalData = [];

    public function setOriginalData(array $values): void
    {
        $this->originalData = $values;
    }

    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    public function delete(): int
    {
        throw new \Exception('Direct delete is not allowed, use repository\'s delete');
    }

    public function update(iterable $data): bool
    {
        throw new \Exception('Direct update is not allowed, use repository\'s update');
    }
}
