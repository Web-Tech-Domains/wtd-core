<?php

declare(strict_types=1);

namespace WTD\Queue;

final class BatchRepository
{
    /**
     * @var array<string, Batch>
     */
    private array $batches = [];

    /**
     * @param list<Job> $jobs
     */
    public function create(array $jobs, QueueDriver $driver, string $queue = 'default'): Batch
    {
        $batch = new Batch(bin2hex(random_bytes(12)), count($jobs), count($jobs));
        $this->batches[$batch->id] = $batch;

        foreach ($jobs as $job) {
            $driver->push($job, $queue, batchId: $batch->id);
        }

        return $batch;
    }

    public function find(string $id): ?Batch
    {
        return $this->batches[$id] ?? null;
    }
}
