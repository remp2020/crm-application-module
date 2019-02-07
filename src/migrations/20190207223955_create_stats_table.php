<?php


use Phinx\Migration\AbstractMigration;

class CreateStatsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stats')
            ->addColumn('key', 'string', array('null' => false))
            ->addColumn('value', 'string')
            ->addIndex('key', array('unique' => true))
            ->create();
    }
}
