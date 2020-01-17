<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Populator\AbstractPopulator;
use Faker\Factory as FakerFactory;
use Nette;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulatorCommand extends Command
{
    /** @var Nette\Database\Context */
    private $database;

    /** @var \Faker\Generator */
    private $faker;

    /** @var AbstractPopulator[] */
    private $seeders = [];

    /**
     *
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
//      $this->faker = FakerFactory::create('sk_SK');
        $this->faker = FakerFactory::create('en_EN');
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->setName('application:populate')
            ->setDescription('Populate data to system');
    }

    /**
     * Add new seeder
     * @param AbstractPopulator $seeder
     */
    public function addSeeder(AbstractPopulator $seeder)
    {
        $seeder->setPopulator($this);
        $seeder->setDatabase($this->database);
        $seeder->setFaker($this->faker);
        $this->seeders[] = $seeder;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $this->registerStyles($output);

        ProgressBar::setFormatDefinition(
            'custom',
            "<info>%populating%</info>\n<yellow>%message%</yellow>\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%"
        );

        $output->writeln('');
        $output->writeln('<info>***** POPULATOR *****</info>');
        $output->writeln('');

        foreach ($this->seeders as $seeder) {
            $output->writeln('');
            $output->writeln('');
            $progressBar = new ProgressBar($output, $seeder->getCount());
            $progressBar->setFormat('custom');
            $progressBar->start();
            $progressBar->setMessage('Populating *' . $seeder->getName() . '*', 'populating');
            $progressBar->setMessage('in progress ...');

            $seeder->seed($progressBar);

            $progressBar->setMessage('done');
            $progressBar->finish();
            $output->writeln('');
        }

        $end = microtime(true);
        $duration = $end - $start;

        $output->writeln('');
        $output->writeln('<yellow>All done. Took ' . round($duration, 2) . ' sec.</yellow>');
        $output->writeln('');

        return 0;
    }

    /**
     * @param OutputInterface $output
     */
    protected function registerStyles($output)
    {
        $output->getFormatter()->setStyle('red', new OutputFormatterStyle('red', 'black'));
        $output->getFormatter()->setStyle('yellow', new OutputFormatterStyle('yellow', 'black'));
    }
}
