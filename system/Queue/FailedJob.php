<?php

declare(strict_types=1);

namespace WTD\Queue;

use Throwable;

final class FailedJob
{
    public function __construct(
        public readonly QueuedJob $job,
        public readonly Throwable $exception,
        public readonly int $failedAt,
    ) {
    }
}
