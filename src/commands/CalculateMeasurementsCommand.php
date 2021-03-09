<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\Helpers\UserDateHelper;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Day;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Month;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Week;
use Crm\ApplicationModule\Models\Measurements\Aggregation\Year;
use Crm\ApplicationModule\Models\Measurements\Criteria;
use Crm\ApplicationModule\Models\Measurements\MeasurementManager;
use Crm\ApplicationModule\Models\Measurements\Repository\MeasurementValuesRepository;
use Crm\ApplicationModule\Models\Measurements\Repository\MeasurementsRepository;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateMeasurementsCommand extends Command
{
    private MeasurementManager $measurementStorage;
    private MeasurementValuesRepository $measurementValuesRepository;
    private MeasurementsRepository $measurementsRepository;
    private ConfigsRepository $configsRepository;
    private UserDateHelper $userDateHelper;

    public function __construct(
        MeasurementManager $measurementStorage,
        MeasurementValuesRepository $measurementValuesRepository,
        MeasurementsRepository $measurementsRepository,
        ConfigsRepository $configsRepository,
        UserDateHelper $userDateHelper
    ) {
        parent::__construct();
        $this->measurementStorage = $measurementStorage;
        $this->measurementValuesRepository = $measurementValuesRepository;
        $this->measurementsRepository = $measurementsRepository;
        $this->configsRepository = $configsRepository;
        $this->userDateHelper = $userDateHelper;
    }

    protected function configure()
    {
        $this->setName('application:calculate-measurement')
            ->setDescription('Calculate measurement')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED)
            ->addOption('to', 't', InputOption::VALUE_REQUIRED)
            ->addOption('measurement', 'm', InputOption::VALUE_REQUIRED, 'Specific measurement to calculate')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'Prints list of available measurements and halts')
            ->addOption('daily', 'd', InputOption::VALUE_NONE, 'Runs the command in the minimal required timeframe in order to correctly update measurements for all aggregations.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $measurements = $this->measurementStorage->getMeasurements();
        $aggregations = [new Year(), new Month(), new Week(), new Day()];

        // Determine when CRM was installed based on the oldest config record in the database. Not 100% accurate,
        // but should be good enough.
        $epochConfig = $this->configsRepository->getTable()->order('created_at ASC')->limit(1)->fetch();
        $epoch = $epochConfig->created_at;

        if ($input->getOption('list')) {
            $output->writeln("Printing available measurements:");
            foreach ($measurements as $code => $_) {
                $output->writeln("  * {$code}");
            }
            return Command::SUCCESS;
        }

        if ($input->getOption('from')) {
            $startDay = DateTime::from($input->getOption('from'));
        } elseif ($input->getOption('daily')) {
            $startDay = DateTime::from('first day of january this year');
        } else {
            $startDay = $epoch;
        }

        if ($input->getOption('to')) {
            $endDay = DateTime::from($input->getOption('to'));
        } else {
            $endDay = new DateTime();
        }
        if ($endDay > new DateTime()) {
            $output->writeln("End time was set to the future, using 'now' to avoid Regenerating measurement data from <info>%s</info> to <info>%s</info>.");
            $endDay = new DateTime();
        }

        $selectedMeasurement = $input->getOption('measurement');
        if ($selectedMeasurement && !isset($measurements[$selectedMeasurement])) {
            $output->writeln("<error>ERROR:</error> Measurement <info>{$selectedMeasurement}</info> doesn't exist.");
            return Command::FAILURE;
        }

        $output->writeln(sprintf(
            "Regenerating measurement data from <info>%s</info> to <info>%s</info>.",
            $this->userDateHelper->process($startDay),
            $this->userDateHelper->process($endDay)
        ));

        foreach ($measurements as $measurement) {
            if ($selectedMeasurement && $selectedMeasurement !== $measurement->code()) {
                continue;
            }
            $measurementRow = $this->measurementsRepository->findByCode($measurement->code());

            $output->writeln(" * Measurement <info>{$measurement->code()}</info>");
            $output->writeln("   - clearing values");
            $this->measurementValuesRepository->deleteValues($measurementRow, $startDay, $endDay);

            foreach ($aggregations as $aggregation) {
                $aggString = get_class($aggregation);
                $output->write("   - calculating <info>{$aggString}</info>: ");

                $criteria = new Criteria($aggregation, $epoch, $startDay, $endDay);
                $series = $measurement->calculate($criteria);

                if (!count($series->points())) {
                    $output->writeln('Skipped');
                    continue;
                }

                foreach ($series->points() as $pointItem) {
                    $this->measurementValuesRepository->add($measurementRow, $pointItem, $epoch);
                }
                $output->writeln('OK (' . count($series->points()) . ')');
            }
        }

        return Command::SUCCESS;
    }
}
