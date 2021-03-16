<?php

namespace Crm\ApplicationModule;

/**
 * Trait serving as getter and setter of $now value, useful for testing and controlling time in components.
 *
 * If `$now` is pre-set by `setNow()`, this pre-set `\DateTime` is returned with each `getNow()` call.
 * Otherwise `getNow()` returns always fresh `\DateTime('now')`.
 */
trait NowTrait
{
    /** @var \DateTime */
    private $now;

    public function setNow(\DateTime $now)
    {
        $this->now = $now;
    }

    /**
     * Get current 'now' or cloned value pre-set by `setNow()`
     * @return \DateTime
     */
    public function getNow(): \DateTime
    {
        if ($this->now) {
            return clone $this->now;
        }

        return new \DateTime('now');
    }
}
