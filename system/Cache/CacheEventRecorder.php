<?php

declare(strict_types=1);

namespace WTD\Cache;

final class CacheEventRecorder
{
    /**
     * @var list<CacheEvent>
     */
    private array $events = [];

    public function record(string $type, string $key): void
    {
        $this->events[] = new CacheEvent($type, $key);
    }

    /**
     * @return list<CacheEvent>
     */
    public function events(): array
    {
        return $this->events;
    }
}
