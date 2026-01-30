<?php

namespace toubilib\infra\messaging;

use toubilib\core\domain\ports\EventPublisherInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPEventPublisher implements EventPublisherInterface
{
    private string $host;
    private int $port;
    private string $user;
    private string $pass;
    private string $exchangeName;

    public function __construct(
        string $host = 'rabbitmq',
        int $port = 5672,
        string $user = 'guest',
        string $pass = 'guest',
        string $exchangeName = 'toubilib.events'
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->exchangeName = $exchangeName;
    }

    public function publish(string $routingKey, array $messageData): void
    {
        $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->pass);
        $channel = $connection->channel();

        $messageBody = json_encode($messageData);
        $message = new AMQPMessage($messageBody, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $channel->basic_publish($message, $this->exchangeName, $routingKey);

        $channel->close();
        $connection->close();
    }
}
