<?php

namespace Crm\ApplicationModule\Commands;

use DirectoryIterator;
use http\Exception\RuntimeException;
use Odan\Migration\Command\GenerateCommand;
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
            throw new RuntimeException('Missing migration paths');
        }

        // GenerateCommand also uses only first migration path
        $migrationPath = $migrationPaths[0];
        // Remove previous initial migration + schema.php
        foreach (new DirectoryIterator($migrationPath) as $fileInfo) {
            if(!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }

        // Setting up default options
        $input->setOption('name', 'AllTables');
        $input->setOption('overwrite', 'y');

        return parent::execute($input, $output);
    }
}
