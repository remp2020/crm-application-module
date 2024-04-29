<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Scenario;

use Exception;

interface TriggerHandlerInterface
{
    /**
     * Human-readable name of trigger.
     */
    public function getName(): string;

    /**
     * Unique key for machine-recognizable trigger identification (e.g. `user_registered`)
     */
    public function getKey(): string;

    /**
     * Source hermes event key which this trigger will listen to.
     */
    public function getEventType(): string;

    /**
     * List of params which must be present in TriggerData payload from handleEvent() method.
     *
     * @return string[]
     */
    public function getOutputParams(): array;

    /**
     * @params array $data Payload from hermes event
     * @return TriggerData Data which will be passed to scenario evaluation
     * @throws SkipTriggerException When trigger should be peacefully skipped
     * @throws Exception
     */
    public function handleEvent(array $data): TriggerData;
}
