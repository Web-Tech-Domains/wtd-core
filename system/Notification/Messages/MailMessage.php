<?php

declare(strict_types=1);

namespace WTD\Notification\Messages;

final class MailMessage
{
    public function __construct(
        public readonly string $subject,
        public readonly string $body,
    ) {
    }
}
