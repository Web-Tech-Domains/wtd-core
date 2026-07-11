<?php

declare(strict_types=1);

namespace WTD\Mail;

class InMemoryTransport implements MailTransport
{
    /**
     * @var list<MailMessage>
     */
    private array $sent = [];

    public function send(MailMessage $message): void
    {
        $this->sent[] = $message;
    }

    /**
     * @return list<MailMessage>
     */
    public function sent(): array
    {
        return $this->sent;
    }
}
