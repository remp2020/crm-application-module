<?php

namespace Crm\ApplicationModule\Seeders;

use Crm\ApplicationModule\Repositories\MeasurementsRepository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property MeasurementsRepository $measurementsRepository
 */
trait MeasurementsTrait
{
    private function addMeasurement(OutputInterface $output, string $code, string $title, string $description)
    {
        $measurement = $this->measurementsRepository->findByCode($code);
        if (!$measurement) {
            $this->measurementsRepository->add(
                $code,
                $title,
                $description,
            );
            $output->writeln("  <comment>* measurement <info>$code</info> created</comment>");
            return;
        }

        $output->writeln("  * measurement <info>$code</info> exists");
    }
}
