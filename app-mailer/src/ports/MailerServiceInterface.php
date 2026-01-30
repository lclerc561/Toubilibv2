<?php

namespace toubilib\ports;

interface MailerServiceInterface
{
    /**
     * Envoie un email
     *
     * @param string $to Email destinataire
     * @param string $subject Sujet
     * @param string $body Corps du message (HTML)
     * @return void
     */
    public function sendMail(string $to, string $subject, string $body): void;
}
