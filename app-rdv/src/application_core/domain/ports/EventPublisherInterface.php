<?php

namespace toubilib\core\domain\ports;

interface EventPublisherInterface
{
    /**
     * Publie un événement vers le système de messaging
     *
     * @param string $routingKey Routing key (ex: rdv.created.patient)
     * @param array $messageData Données du message
     * @return void
     */
    public function publish(string $routingKey, array $messageData): void;
}
