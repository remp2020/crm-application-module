<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\Core;
use Phinx\Console\Command\AbstractCommand;
use Phinx\Console\Command\Migrate;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class LoadSchemaForTests extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('schema:load');
        $this->setDescription(
            'Drops and recreates the database, then loads schema dumped by phinx:dump-schema-for-tests',
        );
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Drop and recreate the database without user confirmation.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // the command drops whatever database is configured, keep it usable only against the test one
        if (Core::env('CRM_ENV') !== 'test') {
            $output->writeln(
                '<error>schema:load runs only with CRM_ENV=test, refusing to drop the configured database.</error>',
            );
            return self::CODE_ERROR;
        }

        $config = $this->getConfig();
        $environment = $config->getEnvironment($config->getDefaultEnvironment());
        $database = $environment['name'];

        if (!$input->getOption('force')) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                "<question>Database `{$database}` on host `{$environment['host']}` " .
                'will be dropped and recreated. Continue (y/N)?</question> ',
                false,
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln(
                    'Schema load cancelled, database left untouched. Use --force to skip the confirmation.',
                );
                return self::CODE_ERROR;
            }
        }

        DumpSchemaForTests::recreateDatabase($environment, $database);
        $output->writeln("<info>recreated empty database</info> {$database}");

        $migrate = new Migrate();
        $migrate->setConfig($config);

        return $migrate->run(new ArrayInput([]), $output);
    }
}
