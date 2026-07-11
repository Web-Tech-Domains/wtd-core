<?php

declare(strict_types=1);

namespace WTD\Queue;

/**
 * Represents a job stored on a queue.
 */
final class QueuedJob
{
    public function __construct(
        public readonly string $id,
        public readonly Job $job,
        public readonly string $queue,
        public readonly int $attempts = 0,
        public readonly int $availableAt = 0,
        public readonly int $priority = 0,
        public readonly ?string $batchId = null,
    ) {
    }

    public function withAttempt(int $availableAt = 0): self
    {
        return new self(
            $this->id,
            $this->job,
            $this->queue,
            $this->attempts + 1,
            $availableAt,
            $this->priority,
            $this->batchId,
        );
    }
}
