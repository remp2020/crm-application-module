<?php

namespace Crm\ApplicationModule\Seeders;

use Nette\Database\Context;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarSeeder implements ISeeder
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function seed(OutputInterface $output)
    {
        $lastID = $this->database->query('SELECT `id` FROM `calendar` ORDER BY `id` DESC LIMIT 1;')->fetch();

        if (!$lastID) {
            $calendarData = file_get_contents(dirname(__FILE__) . '/sql/calendar.sql');
            if ($calendarData === false) {
                $output->writeln("  <error>* unable to load file `/sql/calendar.sql` with calendar seed</error>");
                throw new \Exception('Unable to load file `/sql/calendar.sql` with calendar seed');
            }
            $this->database->query($calendarData);
            $output->writeln("  <comment>* calendar seeded with dates from `2014-10-01` to `2024-12-31`</comment>");
        } elseif ($lastID->id !== 20241231) {
            $output->writeln("  <error>* last entry of calendar differs from last entry of seeder's SQL</error>");
        } else {
            $output->writeln("  * calendar already seeded, nothing to do");
        }
    }
}
