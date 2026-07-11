<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * Priority-aware in-memory queue driver used by local, test, and adapter drivers.
 */
class InMemoryQueueDriver implements QueueDriver
{
    /**
     * @var array<string, list<QueuedJob>>
     */
    private array $queues = [];

    public function push(Job $job, string $queue = 'default', int $delaySeconds = 0, int $priority = 0, ?string $batchId = null): string
    {
        $id = bin2hex(random_bytes(16));
        $this->queues[$queue][] = new QueuedJob($id, $job, $queue, availableAt: time() + $delaySeconds, priority: $priority, batchId: $batchId);

        return $id;
    }

    public function pop(string $queue = 'default'): ?QueuedJob
    {
        $jobs = $this->queues[$queue] ?? [];
        $available = [];

        foreach ($jobs as $index => $job) {
            if ($job->availableAt <= time()) {
                $available[$index] = $job;
            }
        }

        if ($available === []) {
            return null;
        }

        uasort($available, static fn (QueuedJob $left, QueuedJob $right): int => $right->priority <=> $left->priority);
        $index = array_key_first($available);
        $job = $available[$index];
        array_splice($this->queues[$queue], (int) $index, 1);

        return $job;
    }

    public function release(QueuedJob $job, int $delaySeconds = 0): void
    {
        $this->queues[$job->queue][] = $job->withAttempt(time() + $delaySeconds);
    }

    public function size(string $queue = 'default'): int
    {
        return count($this->queues[$queue] ?? []);
    }
}
