<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Seeders;

use Nette\Database\Explorer;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarSeeder implements ISeeder
{
    public function __construct(
        private Explorer $database,
    ) {
    }

    public function seed(OutputInterface $output)
    {
        $lastRecord = $this->database->query('SELECT * FROM `calendar` ORDER BY `id` DESC LIMIT 1;')->fetch();

        if ($lastRecord) {
            $nextDate = (new \DateTime($lastRecord['date']))->modify('+1 day');
        } else {
            $nextDate = new \DateTime('2014-01-01 00:00:00');
        }
        $thresholdDate = new \DateTime('+7 years');

        $dates = [];
        while ($nextDate <= $thresholdDate) {
            $dates[] = [
                'id' => $nextDate->format('Ymd'),
                'date' => $nextDate->format('Y-m-d'),
                'year' => $nextDate->format('Y'),
                'month' => $nextDate->format('n'),
                'day' => $nextDate->format('d'),
                'quarter' => match ($nextDate->format('n')) {
                    '1','2','3' => '1',
                    '4','5','6' => '2',
                    '7','8','9' => '3',
                    '10','11','12' => '4',
                },
                'week' => $nextDate->format('W'),
            ];

            $nextDate->modify('+1 day');
        }

        $this->database->query('INSERT INTO calendar ', $dates);

        $output->writeln('  <comment>* calendar seeded with dates until ' . $thresholdDate->format('Y-m-d') . '</comment>');
    }
}
