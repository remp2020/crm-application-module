<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddOptionsColumnToConfigsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('configs')
            ->addColumn('options', 'json', ['after' => 'type', 'null' => true])
            ->update();
    }
}
