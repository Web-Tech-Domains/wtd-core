<?php

declare(strict_types=1);

namespace WTD\Queue;

final class Batch
{
    public function __construct(
        public readonly string $id,
        public readonly int $totalJobs,
        public int $pendingJobs,
        public int $failedJobs = 0,
    ) {
    }

    public function recordSuccess(): void
    {
        $this->pendingJobs = max(0, $this->pendingJobs - 1);
    }

    public function recordFailure(): void
    {
        $this->failedJobs++;
        $this->pendingJobs = max(0, $this->pendingJobs - 1);
    }

    public function finished(): bool
    {
        return $this->pendingJobs === 0;
    }
}
