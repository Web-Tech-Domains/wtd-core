<?php

declare(strict_types=1);

namespace WTD\Events;

use RuntimeException;
use WTD\Queue\QueueDriver;

final class Dispatcher
{
    /**
     * @var array<class-string, list<callable|object>>
     */
    private array $listeners = [];

    public function __construct(
        private readonly ?QueueDriver $queue = null,
        private readonly ?Broadcaster $broadcaster = null,
    ) {
    }

    /**
     * @param class-string $event
     */
    public function listen(string $event, callable|object $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function subscribe(EventSubscriber $subscriber): void
    {
        $subscriber->subscribe($this);
    }

    /**
     * @return list<mixed>
     */
    public function dispatch(object $event): array
    {
        if ($event instanceof Broadcastable) {
            $this->broadcaster?->broadcast($event);
        }

        $responses = [];

        foreach ($this->listenersFor($event) as $listener) {
            if ($event instanceof StoppableEvent && $event->isPropagationStopped()) {
                break;
            }

            if ($listener instanceof ShouldQueue && $this->queue !== null) {
                $this->queue->push(new CallQueuedListener($listener, $event));
                continue;
            }

            $responses[] = $this->callListener($listener, $event);
        }

        return $responses;
    }

    /**
     * @return list<callable|object>
     */
    private function listenersFor(object $event): array
    {
        $listeners = [];

        foreach ($this->listeners as $eventClass => $registered) {
            if ($event instanceof $eventClass) {
                array_push($listeners, ...$registered);
            }
        }

        return $listeners;
    }

    private function callListener(callable|object $listener, object $event): mixed
    {
        if (is_callable($listener)) {
            return $listener($event);
        }

        if (!method_exists($listener, 'handle')) {
            throw new RuntimeException('Event listener object must define a handle method.');
        }

        return $listener->handle($event);
    }
}
