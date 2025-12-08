<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTableNameIndexToAuditLogs extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->table('audit_logs')->hasIndex('table_name')) {
            $this->table('audit_logs')
                ->addIndex('table_name')
                ->update();
        }
    }

    public function down(): void
    {
        $this->table('audit_logs')
            ->removeIndex('table_name')
            ->update();
    }
}
