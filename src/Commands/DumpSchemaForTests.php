<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\PhinxRegistrator;
use Nette\Utils\FileSystem;
use PDO;
use Phinx\Console\Command\AbstractCommand;
use Phinx\Console\Command\Migrate;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpSchemaForTests extends AbstractCommand
{
    public const string DUMP_FILE_NAME = 'all_tables.sql';
    public const string STATEMENT_SEPARATOR = "\n-- statement\n";

    protected function configure(): void
    {
        $this->setName('phinx:dump-schema-for-tests');
        $this->setDescription(
            'Builds the schema from migrations of all modules and dumps it into tests/migrations/all_tables.sql',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        $config = $this->getConfig();
        $environment = $config->getEnvironment($config->getDefaultEnvironment());

        $scratchDatabase = $environment['name'] . '_schema_dump';
        $serverPdo = self::recreateDatabase($environment, $scratchDatabase);
        $output->writeln("<info>created scratch database</info> {$scratchDatabase}");

        try {
            $this->migrateToScratchDatabase($scratchDatabase, $output);

            $serverPdo->exec("USE `{$scratchDatabase}`");
            $sqlFile = $this->dumpSql($serverPdo, $environment['migration_table']);
            $output->writeln("<info>dumped schema into</info> {$sqlFile}");
        } finally {
            $serverPdo->exec("DROP DATABASE IF EXISTS `{$scratchDatabase}`");
            $output->writeln("<info>dropped scratch database</info> {$scratchDatabase}");
        }

        return self::CODE_SUCCESS;
    }

    private function sourceMigrationPaths(): array
    {
        $paths = [
            ...$this->getConfig()->getMigrationPaths(),
            ...glob(APP_ROOT . 'app/modules/*/migrations', GLOB_ONLYDIR),
            ...glob(APP_ROOT . 'extensions/*/src/migrations', GLOB_ONLYDIR),
            ...glob(APP_ROOT . 'vendor/remp/crm-*-module/src/migrations', GLOB_ONLYDIR),
        ];

        // vendor/remp/crm-*-module dirs are composer symlinks to extensions/*, realpath dedupes them
        return array_values(array_unique(array_filter(array_map('realpath', $paths))));
    }

    public static function recreateDatabase(array $environment, string $database): PDO
    {
        $dsn = "mysql:host={$environment['host']};port={$environment['port']};charset={$environment['charset']}";
        $pdo = new PDO($dsn, $environment['user'], $environment['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("DROP DATABASE IF EXISTS `{$database}`");
        $pdo->exec(sprintf(
            'CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s',
            $database,
            $environment['charset'],
            $environment['collation'],
        ));

        return $pdo;
    }

    private function migrateToScratchDatabase(string $scratchDatabase, OutputInterface $output): void
    {
        // Some data migrations resolve services through $GLOBALS['application'] (set by bin/command.php).
        // That application is connected to the real configured database, so boot a second application
        // against the scratch database and swap it in for the duration of the migration run.
        $applicationBackup = $GLOBALS['application'];
        $databaseNameBackup = $_ENV['CRM_DB_NAME'];
        $_ENV['CRM_DB_NAME'] = $scratchDatabase;

        try {
            $migrateConfig = PhinxRegistrator::buildConfig($this->sourceMigrationPaths());

            $scratchApplication = new ($applicationBackup::class)();
            $scratchApplication->bootstrap();
            $GLOBALS['application'] = $scratchApplication;

            $migrate = new Migrate();
            $migrate->setConfig($migrateConfig);
            $exitCode = $migrate->run(new ArrayInput([]), $output);
        } finally {
            $GLOBALS['application'] = $applicationBackup;
            $_ENV['CRM_DB_NAME'] = $databaseNameBackup;
        }

        if ($exitCode !== self::CODE_SUCCESS) {
            throw new RuntimeException("Migration of scratch database '{$scratchDatabase}' failed.");
        }
    }

    private function dumpSql(PDO $pdo, string $migrationTable): string
    {
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        sort($tables, SORT_STRING);

        $statements = [];
        foreach ($tables as $table) {
            // skip Phinx's own bookkeeping table, it has no place in the dumped schema
            if ($table === $migrationTable) {
                continue;
            }
            $statements[] = self::normalizeCreateTable(
                $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC)['Create Table'],
            );
        }

        $sqlFile = PhinxRegistrator::TEST_MIGRATIONS_PATH . '/' . self::DUMP_FILE_NAME;
        FileSystem::write($sqlFile, implode(self::STATEMENT_SEPARATOR, $statements) . "\n");

        return $sqlFile;
    }

    /** @internal public only for tests */
    public static function normalizeCreateTable(string $createTable): string
    {
        // strip volatile AUTO_INCREMENT counters; re-indent SHOW CREATE TABLE's 2-space indentation to 4
        return preg_replace(['/ AUTO_INCREMENT=\d+/', '/^ {2}/m'], ['', '    '], $createTable);
    }
}
