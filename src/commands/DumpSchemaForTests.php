<?php

namespace Crm\ApplicationModule\Commands;

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Odan\Migration\Command\GenerateCommand;
use Phinx\Util\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class that uses 'odan/phinx-migrations-generator' package for recreating Phinx migration from DB schema
 * This migration is stored in separate test migration folder and its only used when running tests
 */
class DumpSchemaForTests extends GenerateCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrationPaths = $this->getConfig()->getMigrationPaths();
        if (count($migrationPaths) === 0) {
            throw new \RuntimeException('Missing migration paths');
        }

        // GenerateCommand also uses only first migration path
        $migrationPath = $migrationPaths[0];
        // Remove previous initial migration + schema.php
        foreach (Finder::findFiles('*.php')->in($migrationPath) as $splFileInfo) {
            unlink($splFileInfo->getPathname());
        }

        // Setting up default options
        $input->setOption('name', 'AllTables');
        $input->setOption('overwrite', 'y');

        $toReturn = parent::execute($input, $output);

        // HACK!
        // replace migrate file date to fixed date (epoch beginning), so git is enable to track changes
        $epochBeginning = date(Util::DATE_FORMAT, 0);
        foreach (Finder::findFiles('*_all_tables.php')->in($migrationPath) as $splFileInfo) {
            FileSystem::rename($splFileInfo->getPathname(), $splFileInfo->getPath() . DIRECTORY_SEPARATOR . $epochBeginning . '_all_tables.php');
        }

        return $toReturn;
    }
}
