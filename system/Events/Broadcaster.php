<?php

declare(strict_types=1);

namespace WTD\Events;

final class Broadcaster
{
    /**
     * @var list<array{channel: string, payload: array<string, mixed>}>
     */
    private array $broadcasts = [];

    public function broadcast(Broadcastable $event): void
    {
        $this->broadcasts[] = [
            'channel' => $event->broadcastOn(),
            'payload' => $event->broadcastWith(),
        ];
    }

    /**
     * @return list<array{channel: string, payload: array<string, mixed>}>
     */
    public function broadcasts(): array
    {
        return $this->broadcasts;
    }
}
