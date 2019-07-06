<?php

use Phinx\Migration\AbstractMigration;

class HermesRetry extends AbstractMigration
{
    public function change()
    {
        $this->table('hermes_tasks')
            ->addColumn('retry', 'integer', ['null' => true, 'after' => 'id'])
            ->addColumn('execute_at', 'datetime', ['null' => true])
            ->removeIndex('id')
            ->update();
    }
}
