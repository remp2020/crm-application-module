<?php

namespace Crm\ApplicationModule\Event;

class BeforeEvent
{
    private $id;

    private $userId;

    private $parameters;

    /**
     * @param int $id - Unique identifier of related entity to event
     * @param int $userId - User ID related to event
     * @param array $parameters - Parameters related to event
     */
    public function __construct(
        int $id,
        int $userId,
        array $parameters
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parameters = $parameters;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
