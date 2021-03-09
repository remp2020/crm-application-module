<?php

use Phinx\Migration\AbstractMigration;

class Measurements extends AbstractMigration
{
    public function change()
    {
        $this->table('measurements')
            ->addColumn('code', 'string')
            ->addColumn('title', 'string')
            ->addColumn('description', 'text')
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex('code', ['unique' => true])
            ->create();

        $this->table('measurement_values', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('measurement_id', 'integer')
            ->addColumn('value', 'decimal', ['null' => false, 'scale' => 2, 'precision' => '10'])
            ->addColumn('sorting_day', 'date', ['null' => false])
            ->addColumn('year', 'integer', ['null' => true])
            ->addColumn('month', 'integer', ['null' => true])
            ->addColumn('day', 'integer', ['null' => true])
            ->addColumn('week', 'integer', ['null' => true])
            ->addForeignKey('measurement_id', 'measurements', 'id', ['delete' => 'CASCADE'])
            ->addIndex(['measurement_id', 'sorting_day'])
            ->create();

        $this->table('measurement_groups')
            ->addColumn('measurement_id', 'integer', ['null' => false])
            ->addColumn('title', 'string', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('measurement_id', 'measurements', 'id', ['delete' => 'CASCADE'])
            ->addIndex(['measurement_id', 'title'], ['unique' => true])
            ->create();

        $this->table('measurement_group_values', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('measurement_group_id', 'integer', ['null' => false])
            ->addColumn('measurement_value_id', 'biginteger', ['null' => false])
            ->addColumn('key', 'string', ['null' => true])
            ->addColumn('value', 'decimal', ['null' => false, 'scale' => 2, 'precision' => '10'])
            ->addForeignKey('measurement_value_id', 'measurement_values', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('measurement_group_id', 'measurement_groups', 'id',['delete' => 'CASCADE'])
            ->addIndex(['measurement_group_id', 'measurement_value_id', 'key'], ['unique' => true])
            ->create();

        $this->table('measurement_annotation')
            ->addColumn('title', 'string', ['null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('target_date', 'datetime', ['null' => false])
            ->addColumn('created_at','datetime', ['null' => false])
            ->addColumn('updated_at','datetime', ['null' => false])
            ->addIndex('target_date')
            ->create();
    }
}
