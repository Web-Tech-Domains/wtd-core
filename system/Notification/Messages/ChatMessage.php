<?php

declare(strict_types=1);

namespace WTD\Notification\Messages;

final class ChatMessage
{
    public function __construct(public readonly string $text)
    {
    }
}
