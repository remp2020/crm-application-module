<?php

namespace Crm\ApplicationModule\Seeders;

use Symfony\Component\Console\Output\OutputInterface;

interface ISeeder
{
    public function seed(OutputInterface $output);
}
