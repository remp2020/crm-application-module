<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Models\Scenario;

/**
 * Optional extension of TriggerHandlerInterface for triggers passing sensitive data (e.g. passwords)
 * in their output params.
 *
 * Params are still available to the running scenario jobs, but they are redacted wherever
 * the job is persisted or logged outside the live job (job history, logs).
 */
interface SensitiveTriggerParamsInterface
{
    /**
     * Subset of getOutputParams() which must not be persisted in cleartext.
     *
     * @return string[]
     */
    public function getSensitiveParams(): array;
}
