<?php

declare(strict_types=1);

namespace WTD\Events;

use WTD\Queue\QueueDriver;
use WTD\Support\ServiceProvider;

final class EventsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container()->singleton(Broadcaster::class, fn (): Broadcaster => new Broadcaster());
        $this->container()->singleton(EventDiscovery::class, fn (): EventDiscovery => new EventDiscovery());
        $this->container()->singleton(
            Dispatcher::class,
            fn (): Dispatcher => new Dispatcher(
                $this->container()->has(QueueDriver::class) ? $this->container()->get(QueueDriver::class) : null,
                $this->container()->get(Broadcaster::class),
            ),
        );
    }
}
