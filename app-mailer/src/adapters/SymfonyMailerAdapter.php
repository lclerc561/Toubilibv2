<?php

namespace toubilib\adapters;

use toubilib\ports\MailerServiceInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class SymfonyMailerAdapter implements MailerServiceInterface
{
    private Mailer $mailer;
    private string $fromEmail;

    public function __construct(string $smtpHost, int $smtpPort, string $fromEmail = 'noreply@toubilib.fr')
    {
        $dsn = sprintf('smtp://%s:%d', $smtpHost, $smtpPort);
        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
        $this->fromEmail = $fromEmail;
    }

    public function sendMail(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($to)
            ->subject($subject)
            ->html($body);

        $this->mailer->send($email);
    }
}
