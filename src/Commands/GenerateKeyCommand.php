<?php

namespace Crm\ApplicationModule\Commands;

use Crm\ApplicationModule\Application\Core;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateKeyCommand extends Command
{
    use DecoratedCommandTrait;

    protected function configure()
    {
        $this->setName('application:generate_key')
            ->setDescription('Set the application key')
            ->addOption(
                'force',
                null,
                null,
                'Force the operation to run when in production.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $this->generateRandomKey();

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        if (!$this->setKeyInEnvironmentFile($key)) {
            $this->error('Application key was not set.');
            return Command::FAILURE;
        }

        $this->info('Application key set successfully.');
        return Command::SUCCESS;
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     * @throws \Exception
     */
    protected function generateRandomKey(): string
    {
        // Similar to Laravel's Encrypter (32 random bytes)
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Set the application key in the environment file.
     *
     * @param  string  $key
     * @return bool
     */
    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $currentKey = Core::env('CRM_KEY', '');

        if (strlen($currentKey) !== 0 && !$this->confirmToProceed()) {
            return false;
        }

        Core::writeEnv('CRM_KEY', $key);

        return true;
    }
}
