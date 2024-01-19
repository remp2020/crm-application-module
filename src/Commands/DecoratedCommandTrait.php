<?php

namespace Crm\ApplicationModule\Commands;

use Closure;
use Crm\ApplicationModule\Application\Core;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Useful shortcuts (inspired by Laravel) to work with Symphony input & output
 * Can be used in a command extending Symfony\Component\Console\Command\Command;
 */
trait DecoratedCommandTrait
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * The output interface implementation.
     *
     * @var OutputStyle
     */
    protected $output;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);
        $this->line('**** ' . self::getCommandName() . ' (date: ' . (new DateTime())->format(DATE_RFC3339) . ') ****', 'info');
    }

    private static function getCommandName(): string
    {
        $fullClassName = explode('\\', __CLASS__);
        $class = end($fullClassName);
        $words = array_filter(preg_split('/(?=[A-Z])/', $class));
        $lastWord = array_pop($words);
        if ($lastWord !== 'Command') {
            // put it back
            $words[] = $lastWord;
        }
        return implode(' ', $words);
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string|null  $style
     * @return void
     */
    public function line(string $string, ?string $style = null): void
    {
        $styled = $style ? "<$style>$string</$style>" : $string;
        $this->output->writeln($styled);
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @return void
     */
    public function info(string $string): void
    {
        $this->line($string, 'info');
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @return void
     */
    public function comment(string $string): void
    {
        $this->line($string, 'comment');
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @return void
     */
    public function question(string $string): void
    {
        $this->line($string, 'question');
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @return void
     */
    public function error(string $string): void
    {
        $this->line($string, 'error');
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @return void
     */
    public function warn(string $string): void
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');

            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning');
    }

    /**
     * Write a string in an alert box.
     *
     * @param  string  $string
     * @return void
     */
    public function alert(string $string): void
    {
        $length = strlen(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', $length));

        $this->output->newLine();
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string  $question
     * @param  bool    $default
     * @return bool
     */
    public function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Confirm before proceeding with the action.
     *
     * This method only asks for confirmation in production.
     *
     * @param  string  $warning
     * @param  Closure|bool|null  $callback
     * @return bool
     */
    public function confirmToProceed(string $warning = 'Application In Production!', $callback = null): bool
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;

        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;

        if ($shouldConfirm) {
            if ($this->input->hasOption('force') && $this->input->getOption('force')) {
                return true;
            }

            $this->alert($warning);

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        return true;
    }

    /**
     * Get the default confirmation callback (if app is run in production)
     *
     * @return Closure
     */
    protected function getDefaultConfirmCallback(): Closure
    {
        return function () {
            return Core::env('CRM_ENV') !== 'local';
        };
    }
}
