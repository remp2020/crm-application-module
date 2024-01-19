<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\EnvironmentConfig;
use Crm\ApplicationModule\Models\Redis\RedisClientFactory;
use Crm\ApplicationModule\Models\Redis\RedisClientTrait;
use Crm\ApplicationModule\Repositories\AuditLogRepository;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateAuditLogsCommand extends Command
{
    use RedisClientTrait;

    public const AUDIT_LOGS_MIGRATION_RUNNING = 'audit_logs_migration_running';

    public const COMMAND_NAME = "application:convert_audit_logs_to_bigint";

    public function __construct(
        private Explorer $database,
        private AuditLogRepository $auditLogRepository,
        private EnvironmentConfig $environmentConfig,
        RedisClientFactory $redisClientFactory,
    ) {
        parent::__construct();

        $this->redisClientFactory = $redisClientFactory;
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Migrate audit logs data to new table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('STARTING `audit_logs` TABLE DATA MIGRATION');
        $output->writeln('');

        $auditLogRepositoryTableName = $this->auditLogRepository->getTable()->getName();
        $auditLogRepositoryV2TableName = $this->auditLogRepository->getNewTable()->getName();

        // Set migration running/start time flag in redis
        $migrationStartTime = new DateTime();
        if ($this->redis()->exists(self::AUDIT_LOGS_MIGRATION_RUNNING)) {
            $migrationStartTime = new DateTime($this->redis()->get(self::AUDIT_LOGS_MIGRATION_RUNNING));
        } else {
            $this->redis()->set(self::AUDIT_LOGS_MIGRATION_RUNNING, $migrationStartTime);
        }

        $this->database->query("
            SET FOREIGN_KEY_CHECKS=0;
            SET UNIQUE_CHECKS=0;
        ");

        // Paging LOOP
        $pageSize = 10000;
        while (true) {
            $lastMigratedId = $this->database
                ->query("SELECT id FROM `{$auditLogRepositoryV2TableName}` WHERE created_at <= ? ORDER BY id DESC LIMIT 1", $migrationStartTime)
                ->fetch()
                ?->id ?? 0;

            $maxId = $this->database
                ->query("SELECT id FROM `{$auditLogRepositoryTableName}` WHERE created_at <= ? ORDER BY id DESC LIMIT 1", $migrationStartTime)
                ->fetch()
                ?->id ?? 0;

            if ($maxId === 0 || $lastMigratedId === $maxId) {
                break;
            }

            $this->database->query("
                INSERT IGNORE INTO `{$auditLogRepositoryV2TableName}` (`id`, `operation`, `user_id`, `table_name`, `signature`, `data`, `created_at`, `deleted_at`)
                SELECT `id`, `operation`, `user_id`, `table_name`, `signature`, `data`, `created_at`, `deleted_at`
                FROM `{$auditLogRepositoryTableName}`
                WHERE id > {$lastMigratedId}
                ORDER BY id ASC
                LIMIT {$pageSize}
            ");

            $remaining = $maxId-$lastMigratedId;
            $output->write("\r\e[0KMIGRATED IDs: {$lastMigratedId} / {$maxId} (REMAINING: {$remaining})");
        }

        $output->writeln('');
        $output->writeln('DATA MIGRATED');
        $output->writeln('');
        $output->writeln('UPDATING ROWS DIFFERENCES AND INSERTING MISSING ROWS');

        $this->fixTableDifferences(
            $auditLogRepositoryTableName,
            $auditLogRepositoryV2TableName,
            $migrationStartTime
        );

        $output->writeln('');
        $output->writeln('SETUPING AUTO_INCREMENT');

        // Sat AUTO_INCREMENT for new tables to old table values
        $dbName = $this->environmentConfig->get('DB_NAME');
        $this->database->query("
            SELECT MAX(id)+10000 INTO @AutoInc FROM {$auditLogRepositoryTableName};

            SET @s:=CONCAT('ALTER TABLE `{$dbName}`.`{$auditLogRepositoryV2TableName}` AUTO_INCREMENT=', @AutoInc);
            PREPARE stmt FROM @s;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");

        $output->writeln('');
        $output->writeln('RENAMING TABLES');

        // Rename tables
        $this->database->query("
            ANALYZE TABLE {$auditLogRepositoryV2TableName};
            RENAME TABLE {$auditLogRepositoryTableName} TO {$auditLogRepositoryTableName}_old,
            {$auditLogRepositoryV2TableName} TO {$auditLogRepositoryTableName};
        ");

        $output->writeln('');
        $output->writeln('UPDATING ROWS DIFFERENCES AND INSERTING MISSING ROWS');

        $this->fixTableDifferences(
            $auditLogRepositoryTableName . '_old',
            $auditLogRepositoryTableName,
            $migrationStartTime
        );

        $this->database->query("
            SET FOREIGN_KEY_CHECKS=1;
            SET UNIQUE_CHECKS=1;
        ");

        // Remove migration running flag in redis
        $this->redis()->del(self::AUDIT_LOGS_MIGRATION_RUNNING);

        $output->writeln('');
        $output->writeln('DATA MIGRATED SUCCESSFULLY');
        return Command::SUCCESS;
    }

    public function fixTableDifferences(
        string $fromTable,
        string $toTable,
        DateTime $updatedAfter
    ) {
        $this->database->query("
            UPDATE {$toTable} al_to
            JOIN {$fromTable} al_from on al_to.id = al_from.id
            SET al_to.deleted_at = al_from.deleted_at
            WHERE al_to.deleted_at != al_from.deleted_at;
        ");

        $missingIds = $this->database->query("
            SELECT `id` FROM `{$fromTable}`
            WHERE created_at > ?
            AND `id` NOT IN (
                SELECT `id` FROM `{$toTable}` WHERE created_at > ?
            )
        ", $updatedAfter, $updatedAfter)->fetchFields();

        if ($missingIds) {
            $this->database->query("
                INSERT IGNORE INTO `{$toTable}` (`id`, `operation`, `user_id`, `table_name`, `signature`, `data`, `created_at`, `deleted_at`)
                SELECT `id`, `operation`, `user_id`, `table_name`, `signature`, `data`, `created_at`, `deleted_at`
                FROM `{$fromTable}`
                WHERE `id` IN ?
            ", $missingIds);
        }
    }
}
