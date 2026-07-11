<?php

declare(strict_types=1);

namespace WTD\Events;

use RuntimeException;
use WTD\Queue\Job;

final class CallQueuedListener implements Job
{
    public function __construct(
        private readonly object $listener,
        private readonly object $event,
    ) {
    }

    public function handle(): void
    {
        if (!method_exists($this->listener, 'handle')) {
            throw new RuntimeException('Queued event listener must define a handle method.');
        }

        $this->listener->handle($this->event);
    }
}
