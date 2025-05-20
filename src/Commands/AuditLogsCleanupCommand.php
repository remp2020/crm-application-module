<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Repositories\AuditLogRepository;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AuditLogsCleanupCommand extends Command
{
    use DecoratedCommandTrait;

    public function __construct(
        private AuditLogRepository $auditLogRepository,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('application:audit_logs_cleanup')
            ->setDescription('Deletes old soft deleted audit logs.')
            ->addOption(
                'days',
                null,
                InputOption::VALUE_REQUIRED,
                "Delete audit logs that have `deleted_at` older than number of days (default: 14)",
                14,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getOption('days');

        $dateLimit = DateTime::from("-{$days} days");

        $rowsToDelete = $this->auditLogRepository->getTable()
            ->where('deleted_at < ?', $dateLimit)
            ->delete();

        $this->info("Deleted {$rowsToDelete} audit log rows.");

        $this->info('***** AUDIT LOGS CLEANUP DONE *****');
        return Command::SUCCESS;
    }
}
