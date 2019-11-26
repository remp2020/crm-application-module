<?php

use Phinx\Migration\AbstractMigration;

class AuditLogIndex extends AbstractMigration
{
    public function change()
    {
        $this->table('audit_logs')
            ->addIndex('created_at')
            ->update();
    }
}
