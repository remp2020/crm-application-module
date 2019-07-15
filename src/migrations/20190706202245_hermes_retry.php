<?php

use Phinx\Migration\AbstractMigration;

class HermesRetry extends AbstractMigration
{
    public function change()
    {
        $this->table('hermes_tasks')
            ->removeIndex('id')
            ->addColumn('retry', 'integer', ['null' => true, 'after' => 'id'])
            ->addColumn('execute_at', 'datetime', ['null' => true])
            ->update();

        $this->table("hermes_tasks")
            ->addColumn('primary_id', 'integer', ['null' => false])
            ->update();

        $sql = <<<SQL
SET @ordering = 100000;
UPDATE hermes_tasks SET primary_id = (@ordering := @ordering + 1) ORDER BY created_at;
SQL;
        $this->execute($sql);

        $this->table('hermes_tasks')
            ->changePrimaryKey('primary_id')
            ->update();

        $this->table('hermes_tasks')
            ->changeColumn('primary_id', 'integer', ['null' => false, 'identity' => true])
            ->update();

        $result = $this->query("SELECT COUNT(*) AS increment FROM hermes_tasks")->fetch();
        $this->execute("ALTER TABLE hermes_tasks AUTO_INCREMENT=" . ($result["increment"] + 200000));

        $this->table('hermes_tasks')
            ->renameColumn("id", "message_id")
            ->update();

        $this->table('hermes_tasks')
            ->renameColumn("primary_id", "id")
            ->update();
    }
}
