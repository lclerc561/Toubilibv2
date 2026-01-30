<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

echo "=== Consommateur de messages RabbitMQ ===\n\n";

// Connexion RabbitMQ
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Déclaration queue (au cas où)
$channel->queue_declare('email.notifications', false, true, false, false);

echo "En attente de messages...\n\n";

// Callback quand message reçu
$callback = function ($msg) {
    echo "Message reçu:\n";
    $data = json_decode($msg->body, true);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "---\n\n";

    $msg->ack();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('email.notifications', '', false, false, false, false, $callback);

// Boucle d'écoute
while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
