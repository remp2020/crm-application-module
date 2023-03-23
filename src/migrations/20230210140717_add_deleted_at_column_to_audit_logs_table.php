<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDeletedAtColumnToAuditLogsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('audit_logs')
            ->addColumn('deleted_at', 'datetime', ['null' => true])
            ->save();
    }
}
