<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use toubilib\adapters\SymfonyMailerAdapter;

echo "=== Consommateur de messages avec envoi d'emails ===\n\n";

// Configuration depuis variables d'environnement
$smtpHost = getenv('SMTP_HOST') ?: 'mailcatcher';
$smtpPort = (int)(getenv('SMTP_PORT') ?: 1025);

// Service d'envoi de mail (via interface)
$mailerService = new SymfonyMailerAdapter($smtpHost, $smtpPort);

// Connexion RabbitMQ
$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('email.notifications', false, true, false, false);

echo "Configuration:\n";
echo "- SMTP: $smtpHost:$smtpPort\n";
echo "- Queue: email.notifications\n\n";
echo "En attente de messages...\n\n";

// Callback traitement message
$callback = function ($msg) use ($mailerService) {
    $data = json_decode($msg->body, true);

    echo "[" . date('Y-m-d H:i:s') . "] Message reçu\n";

    try {
        $eventType = $data['eventType'] ?? 'unknown';
        $recipient = $data['recipient'] ?? [];
        $eventData = $data['data'] ?? [];

        $to = $recipient['email'] ?? null;
        if (!$to) {
            echo "❌ Erreur: pas d'email destinataire\n\n";
            $msg->ack();
            return;
        }

        // Construire sujet et corps selon type événement
        if (str_contains($eventType, 'created.patient')) {
            $subject = "Confirmation de votre rendez-vous";
            $body = sprintf(
                "<h2>Bonjour %s %s,</h2>
                <p>Votre rendez-vous a été confirmé:</p>
                <ul>
                    <li><strong>Date:</strong> %s</li>
                    <li><strong>Durée:</strong> %d minutes</li>
                    <li><strong>Motif:</strong> %s</li>
                    <li><strong>Praticien:</strong> Dr. %s %s (%s)</li>
                </ul>
                <p>À bientôt!</p>",
                $recipient['prenom'],
                $recipient['nom'],
                $eventData['dateHeureDebut'],
                $eventData['duree'],
                $eventData['motifVisite'],
                $eventData['praticien']['prenom'] ?? '',
                $eventData['praticien']['nom'] ?? '',
                $eventData['praticien']['specialite'] ?? ''
            );
        } elseif (str_contains($eventType, 'created.praticien')) {
            $subject = "Nouveau rendez-vous";
            $body = sprintf(
                "<h2>Bonjour Dr. %s,</h2>
                <p>Un nouveau rendez-vous a été créé:</p>
                <ul>
                    <li><strong>Date:</strong> %s</li>
                    <li><strong>Durée:</strong> %d minutes</li>
                    <li><strong>Motif:</strong> %s</li>
                    <li><strong>Patient:</strong> %s %s</li>
                </ul>",
                $recipient['nom'],
                $eventData['dateHeureDebut'],
                $eventData['duree'],
                $eventData['motifVisite'],
                $eventData['patient']['prenom'] ?? '',
                $eventData['patient']['nom'] ?? ''
            );
        } elseif (str_contains($eventType, 'cancelled.patient')) {
            $subject = "Annulation de votre rendez-vous";
            $body = sprintf(
                "<h2>Bonjour %s %s,</h2>
                <p><strong>Votre rendez-vous a été annulé:</strong></p>
                <ul>
                    <li><strong>Date:</strong> %s</li>
                    <li><strong>Durée:</strong> %d minutes</li>
                    <li><strong>Motif:</strong> %s</li>
                    <li><strong>Praticien:</strong> Dr. %s %s (%s)</li>
                </ul>
                <p>Si vous souhaitez reprendre rendez-vous, n'hésitez pas à nous contacter.</p>",
                $recipient['prenom'],
                $recipient['nom'],
                $eventData['dateHeureDebut'],
                $eventData['duree'],
                $eventData['motifVisite'],
                $eventData['praticien']['prenom'] ?? '',
                $eventData['praticien']['nom'] ?? '',
                $eventData['praticien']['specialite'] ?? ''
            );
        } elseif (str_contains($eventType, 'cancelled.praticien')) {
            $subject = "Annulation de rendez-vous";
            $body = sprintf(
                "<h2>Bonjour Dr. %s,</h2>
                <p><strong>Un rendez-vous a été annulé:</strong></p>
                <ul>
                    <li><strong>Date:</strong> %s</li>
                    <li><strong>Durée:</strong> %d minutes</li>
                    <li><strong>Motif:</strong> %s</li>
                    <li><strong>Patient:</strong> %s %s</li>
                </ul>",
                $recipient['nom'],
                $eventData['dateHeureDebut'],
                $eventData['duree'],
                $eventData['motifVisite'],
                $eventData['patient']['prenom'] ?? '',
                $eventData['patient']['nom'] ?? ''
            );
        } else {
            $subject = "Notification Toubilib";
            $body = "<p>Un événement s'est produit sur votre compte.</p>";
        }

        // Envoi email
        $mailerService->sendMail($to, $subject, $body);

        echo "✅ Email envoyé à $to\n";
        echo "   Sujet: $subject\n\n";

        $msg->ack();
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n\n";
        $msg->nack(false, true); // Requeue
    }
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('email.notifications', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
