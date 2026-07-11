<?php

declare(strict_types=1);

namespace WTD\Notification\Messages;

final class WebhookMessage
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(public readonly array $payload)
    {
    }
}
