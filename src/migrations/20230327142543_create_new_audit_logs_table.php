<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNewAuditLogsTable extends AbstractMigration
{
    public function up(): void
    {
        $autologinTokensRowCount = $this->query('SELECT 1 FROM audit_logs LIMIT 1;')->fetch();
        if ($autologinTokensRowCount === false) {
            $this->table('audit_logs')
                ->changeColumn('id', 'biginteger', ['identity' => true])
                ->save();
        } else {
            $this->query("
                CREATE TABLE audit_logs_v2 LIKE audit_logs;
            ");

            $this->table('audit_logs_v2')
                ->changeColumn('id', 'biginteger', ['identity' => true])
                ->addForeignKey('user_id', 'users')
                ->save();
        }
    }

    public function down()
    {
        $this->output->writeln('Down migration is not available.');
    }
}
