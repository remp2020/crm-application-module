<?php

namespace Crm\ApplicationModule\Hermes;

use Tomaj\Hermes\Message;
use Tomaj\Hermes\MessageInterface;

class HermesMessage implements MessageInterface
{
    private $internalMessage;

    public function __construct(string $type, array $payload = null, string $messageId = null, float $created = null, $process = false)
    {
        $this->internalMessage = new Message($type, $payload, $messageId, $created, $process);
    }

    /**
     * Message identifier.
     *
     * This identifier should be unique all the time.
     * Recommendation is to use UUIDv4 (Included Message implementation
     * generating UUIDv4 identifiers)
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->internalMessage->getId();
    }

    /**
     * Message creation date - micro timestamp
     *
     * @return string
     */
    public function getCreated(): float
    {
        return $this->internalMessage->getCreated();
    }

    /**
     * Message executing date - microtime(true)
     *
     * @return float
     */
    public function getExecuteAt(): ?float
    {
        return $this->internalMessage->getExecuteAt();
    }

    /**
     * Date when message has to be processed - timestamp
     *
     * @return string
     */
    public function getProcess(): string
    {
        return $this->internalMessage->getExecuteAt();
    }

    /**
     * Message type
     *
     * Based on this field, message will be dispatched and will be sent to
     * appropriate handler.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->internalMessage->getType();
    }

    /**
     * Payload data.
     *
     * This data can be used for anything that you would like to send to handler.
     * Warning! This data has to be serializable to string. Don't put there php resources
     * like database connection resources, file handlers etc..
     *
     * @return array
     */
    public function getPayload(): ?array
    {
        return $this->internalMessage->getPayload();
    }
}
