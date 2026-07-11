<?php

declare(strict_types=1);

namespace WTD\Notification\Messages;

final class FirebaseMessage
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
    ) {
    }
}
