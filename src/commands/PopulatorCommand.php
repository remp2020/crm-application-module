<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Populator\AbstractPopulator;
use Faker\Factory;
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
    private $populators = [];

    /**
     *
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
        $this->faker = Factory::create('en_EN');
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
     * @param AbstractPopulator $populator
     */
    public function addSeeder(AbstractPopulator $populator)
    {
        $populator->setPopulator($this);
        $populator->setDatabase($this->database);
        $populator->setFaker($this->faker);
        $this->populators[] = $populator;
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
            "  * %populating%: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%"
        );

        $output->writeln('');
        $output->writeln('<info>***** POPULATOR *****</info>');
        $output->writeln('');

        foreach ($this->populators as $seeder) {
            $progressBar = new ProgressBar($output, $seeder->getCount());
            $progressBar->setFormat('custom');
            $progressBar->setMessage('Populating <comment>' . $seeder->getName() . '</comment>', 'populating');
            $progressBar->start();

            $seeder->seed($progressBar);
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
