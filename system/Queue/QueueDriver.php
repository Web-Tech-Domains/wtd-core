<?php

declare(strict_types=1);

namespace WTD\Queue;

interface QueueDriver
{
    public function push(Job $job, string $queue = 'default', int $delaySeconds = 0, int $priority = 0, ?string $batchId = null): string;

    public function pop(string $queue = 'default'): ?QueuedJob;

    public function release(QueuedJob $job, int $delaySeconds = 0): void;

    public function size(string $queue = 'default'): int;
}
