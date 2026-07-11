<?php

declare(strict_types=1);

namespace WTD\Notification\Messages;

final class DatabaseMessage
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(public readonly array $data)
    {
    }
}
