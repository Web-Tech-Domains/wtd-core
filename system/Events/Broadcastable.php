<?php

declare(strict_types=1);

namespace WTD\Events;

interface Broadcastable
{
    public function broadcastOn(): string;

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array;
}
