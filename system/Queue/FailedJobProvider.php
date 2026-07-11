<?php

declare(strict_types=1);

namespace WTD\Queue;

use Throwable;

final class FailedJobProvider
{
    /**
     * @var list<FailedJob>
     */
    private array $failed = [];

    public function record(QueuedJob $job, Throwable $exception): void
    {
        $this->failed[] = new FailedJob($job, $exception, time());
    }

    /**
     * @return list<FailedJob>
     */
    public function all(): array
    {
        return $this->failed;
    }

    public function retry(string $id, QueueDriver $driver): bool
    {
        foreach ($this->failed as $index => $failed) {
            if ($failed->job->id !== $id) {
                continue;
            }

            $driver->release($failed->job);
            array_splice($this->failed, $index, 1);

            return true;
        }

        return false;
    }
}
