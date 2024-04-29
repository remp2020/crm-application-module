<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Scenario;

use Crm\ScenariosModule\Engine\Dispatcher as JobDispatcher;
use Crm\ScenariosModule\Repositories\JobsRepository;
use Exception;
use Tomaj\Hermes\Dispatcher;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\MessageInterface;

class TriggerManager implements HandlerInterface
{
    /**
     * @var TriggerHandlerInterface[]
     */
    private array $triggerHandlers = [];

    public function __construct(
        private readonly Dispatcher $hermesDispatcher,
        private readonly JobDispatcher $jobDispatcher
    ) {
    }

    public function getTriggerHandlers(): array
    {
        return $this->triggerHandlers;
    }

    public function getTriggerHandlerByKey(string $key): TriggerHandlerInterface
    {
        foreach ($this->triggerHandlers as $triggerHandler) {
            if ($triggerHandler->getKey() !== $key) {
                continue;
            }

            return $triggerHandler;
        }

        throw new Exception(sprintf(
            "Trigger handler with key '%s' doesn't exist.",
            $key
        ));
    }

    public function registerTriggerHandler(TriggerHandlerInterface $triggerHandler): void
    {
        if (array_key_exists($triggerHandler->getEventType(), $this->triggerHandlers)) {
            throw new Exception(sprintf(
                'Trigger handler %s (%s) is already registered.',
                $triggerHandler->getName(),
                $triggerHandler->getKey()
            ));
        }

        $this->triggerHandlers[$triggerHandler->getEventType()] = $triggerHandler;
        $this->hermesDispatcher->registerHandler($triggerHandler->getEventType(), $this);
    }

    /**
     * @internal Hermes message handler
     */
    public function handle(MessageInterface $message): bool
    {
        if (!array_key_exists($message->getType(), $this->triggerHandlers)) {
            throw new Exception(sprintf(
                "Unknown handler for trigger handler type '%s'",
                $message->getType()
            ));
        }

        $triggerHandler = $this->triggerHandlers[$message->getType()];

        try {
            $triggerData = $triggerHandler->handleEvent($message->getPayload());
            $this->validateTriggerData($triggerHandler, $triggerData);
        } catch (SkipTriggerException) {
            return true;
        } catch (Exception $exception) {
            throw new Exception(sprintf(
                'Error while handling a trigger handler %s: %s',
                $triggerHandler->getName(),
                $exception->getMessage()
            ), previous: $exception);
        }

        $this->jobDispatcher->dispatch($triggerHandler->getKey(), $triggerData->userId, $triggerData->payload, [
            JobsRepository::CONTEXT_HERMES_MESSAGE_TYPE => $triggerHandler->getEventType()
        ]);
        return true;
    }

    private function validateTriggerData(TriggerHandlerInterface $triggerHandler, TriggerData $triggerData): void
    {
        foreach ($triggerHandler->getOutputParams() as $outputParam) {
            if (!array_key_exists($outputParam, $triggerData->payload)) {
                throw new Exception(sprintf(
                    "Output param '%s' is missing in trigger data payload.",
                    $outputParam
                ));
            }
        }

        foreach (array_keys($triggerData->payload) as $outputParam) {
            if (!in_array($outputParam, $triggerHandler->getOutputParams(), strict: true)) {
                throw new Exception(sprintf(
                    "Payload contains an undefined param '%s'.",
                    $outputParam
                ));
            }
        }
    }
}
