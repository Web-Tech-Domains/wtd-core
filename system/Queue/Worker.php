<?php

declare(strict_types=1);

namespace WTD\Queue;

use Throwable;

final class Worker
{
    public function __construct(
        private readonly QueueDriver $driver,
        private readonly FailedJobProvider $failed,
        private readonly BatchRepository $batches,
        private readonly int $maxAttempts = 3,
    ) {
    }

    public function runNext(string $queue = 'default'): bool
    {
        $queued = $this->driver->pop($queue);

        if ($queued === null) {
            return false;
        }

        try {
            $queued->job->handle();
            $this->batches->find((string) $queued->batchId)?->recordSuccess();

            return true;
        } catch (Throwable $exception) {
            if ($queued->attempts + 1 < $this->maxAttempts) {
                $this->driver->release($queued);
                return false;
            }

            $this->failed->record($queued, $exception);
            $this->batches->find((string) $queued->batchId)?->recordFailure();

            return false;
        }
    }
}
