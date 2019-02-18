<?php

namespace Crm\ApplicationModule\Hermes;

use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Tomaj\Hermes\MessageInterface;
use Tomaj\Hermes\SerializerInterface;

class HermesMessageSerializer implements SerializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize(MessageInterface $message): string
    {
        if (method_exists($message, 'getProcess')) {
            $process = $message->getProcess();
        } else {
            $process = false;
            $payload = $message->getPayload();
            if (isset($payload['delayed'])) {
                $process = DateTime::from($payload['delayed'])->getTimestamp();
            }
        }

        $data = [
            'message' => [
                'id' => $message->getId(),
                'type' => $message->getType(),
                'created' => $message->getCreated(),
                'process' => $process,
                'payload' => $message->getPayload(),
                'execute_at' => $process,
            ]
        ];

        return JSON::encode($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize(string $string): MessageInterface
    {
        $data = JSON::decode($string, Json::FORCE_ARRAY);
        $message = $data['message'];
        if (!isset($message['execute_at'])) {
            $message['execute_at'] = $message['process'];
        }
        if (strpos($message['created'], ' ') !== false) {
            $parts = explode(' ', $message['created']);
            $message['created'] = end($parts);
        }
        return new HermesMessage($message['type'], $message['payload'], $message['id'], $message['created'], $message['execute_at']);
    }
}
