<?php

use Phinx\Migration\AbstractMigration;

class AuditLogsAddTableNameAndSignatureIndexes extends AbstractMigration
{
    public function change()
    {
        $this->table('audit_logs')
            ->addIndex('table_name')
            ->addIndex('signature')
            ->update();
    }
}
