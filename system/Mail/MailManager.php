<?php

declare(strict_types=1);

namespace WTD\Mail;

use InvalidArgumentException;

final class MailManager
{
    /**
     * @var array<string, MailTransport>
     */
    private array $transports = [];

    public function __construct(private readonly string $default = 'smtp')
    {
        $this->transports = [
            'smtp' => new SmtpTransport(),
            'ses' => new SesTransport(),
            'mailgun' => new MailgunTransport(),
            'postmark' => new PostmarkTransport(),
            'sendgrid' => new SendGridTransport(),
        ];
    }

    public function extend(string $name, MailTransport $transport): void
    {
        $this->transports[$name] = $transport;
    }

    public function transport(?string $name = null): MailTransport
    {
        $name ??= $this->default;

        return $this->transports[$name] ?? throw new InvalidArgumentException(sprintf('Mail transport [%s] is not configured.', $name));
    }

    public function send(MailMessage $message, ?string $transport = null): void
    {
        $this->transport($transport)->send($message);
    }
}
