<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveAutoloadColumnFromConfigsTable extends AbstractMigration
{
    public function up(): void
    {
        $this->table('configs')
            ->removeColumn('autoload')
            ->update();
    }

    public function down(): void
    {
        $this->table('configs')
            ->addColumn('autoload', 'boolean', array('null' => false, 'default' => true))
            ->update();
    }
}
