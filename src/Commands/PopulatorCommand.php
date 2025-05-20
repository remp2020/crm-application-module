<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Populators\AbstractPopulator;
use Faker\Factory;
use Faker\Generator;
use Nette\Database\Explorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulatorCommand extends Command
{
    use DecoratedCommandTrait;

    private Explorer $database;
    private Generator $faker;

    /** @var AbstractPopulator[] */
    private array $populators = [];

    /**
     *
     * @param Explorer $database
     */
    public function __construct(Explorer $database)
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
            ->setDescription('Populate data to system')
            ->addOption(
                'populator',
                'p',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Filters populators with provided names; multiple options are allowed. (e.g. "-p Autologin -p Payments")',
            );
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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $this->registerStyles($output);

        ProgressBar::setFormatDefinition(
            'custom',
            "  * %populating%: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%",
        );

        $allowedPopulators = array_flip($input->getOption('populator'));
        if (count($allowedPopulators)) {
            $output->writeln('Using filter: ' . implode(', ', array_keys($allowedPopulators)));
        }

        foreach ($this->populators as $seeder) {
            if (count($allowedPopulators) > 0 && !array_key_exists($seeder->getName(), $allowedPopulators)) {
                continue;
            }
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

        return Command::SUCCESS;
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
