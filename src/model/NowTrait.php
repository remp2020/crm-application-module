<?php

namespace Crm\ApplicationModule;

/**
 * Trait serving as getter and setter of $now value, useful for testing and controlling time in components.
 */
trait NowTrait
{
    private $now;

    public function setNow(\DateTime $now)
    {
        $this->now = $now;
    }

    /**
     * Get new $now value (cloned, to avoid modifying original value)
     * @return \DateTime
     * @throws \Exception
     */
    public function getNow(): \DateTime
    {
        if (!$this->now) {
            $this->now = new \DateTime('now');
        }

        return clone $this->now;
    }
}
