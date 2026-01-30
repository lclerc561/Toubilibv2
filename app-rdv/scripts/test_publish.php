<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "=== Test envoi message RabbitMQ ===\n\n";

// Connexion RabbitMQ
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Déclaration exchange
$channel->exchange_declare('toubilib.events', 'topic', false, true, false);

// Déclaration queue
$channel->queue_declare('email.notifications', false, true, false, false);

// Binding
$channel->queue_bind('email.notifications', 'toubilib.events', 'rdv.#');

echo "Exchange, queue et binding créés\n\n";

// Message de test
$messageData = [
    'eventType' => 'rdv.created.patient',
    'rdvId' => 'test-' . uniqid(),
    'recipient' => [
        'type' => 'patient',
        'email' => 'patient@test.fr',
        'nom' => 'Dupont',
        'prenom' => 'Jean'
    ],
    'data' => [
        'dateHeureDebut' => '2026-02-15 14:00:00',
        'duree' => 30,
        'motifVisite' => 'Test',
        'praticien' => [
            'nom' => 'Martin',
            'prenom' => 'Sophie',
            'specialite' => 'Imagerie'
        ]
    ],
    'timestamp' => date('c')
];

$msg = new AMQPMessage(
    json_encode($messageData),
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
);

// Publication
$channel->basic_publish($msg, 'toubilib.events', 'rdv.created.patient');

echo "Message publié:\n";
echo json_encode($messageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

$channel->close();
$connection->close();

echo "\n✅ Test terminé\n";
