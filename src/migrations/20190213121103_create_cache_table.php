<?php


use Phinx\Migration\AbstractMigration;

class CreateCacheTable extends AbstractMigration
{
    public function change()
    {
        $this->table('cache')
            ->addColumn('key', 'string', array('null' => false))
            ->addColumn('value', 'string')
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex('key', array('unique' => true))
            ->create();
    }
}