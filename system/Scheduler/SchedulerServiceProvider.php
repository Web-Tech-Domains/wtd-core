<?php

declare(strict_types=1);

namespace WTD\Scheduler;

use WTD\Support\ServiceProvider;

final class SchedulerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(Mutex::class, fn (): Mutex => new Mutex());
        $this->container()->singleton(Scheduler::class, fn (): Scheduler => new Scheduler($this->container()->get(Mutex::class)));
    }
}
