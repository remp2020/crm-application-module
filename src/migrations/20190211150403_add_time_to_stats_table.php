<?php


use Phinx\Migration\AbstractMigration;

class AddTimeToStatsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stats')
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->save();
    }
}
