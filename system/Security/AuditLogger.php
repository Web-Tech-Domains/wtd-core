<?php

declare(strict_types=1);

namespace WTD\Security;

final class AuditLogger
{
    /**
     * @var list<array<string, mixed>>
     */
    private array $events = [];

    /**
     * @param array<string, mixed> $context
     */
    public function record(string $event, array $context = []): void
    {
        $this->events[] = ['event' => $event, 'context' => $context, 'time' => time()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function events(): array
    {
        return $this->events;
    }
}
