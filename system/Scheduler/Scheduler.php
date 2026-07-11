<?php

declare(strict_types=1);

namespace WTD\Scheduler;

use Closure;
use DateTimeImmutable;

final class Scheduler
{
    /**
     * @var list<Event>
     */
    private array $events = [];

    public function __construct(private readonly Mutex $mutex = new Mutex())
    {
    }

    public function call(string $name, Closure $callback): Event
    {
        $event = new Event($name, $callback);
        $this->events[] = $event;

        return $event;
    }

    /**
     * @return list<Event>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * @return list<string>
     */
    public function dueEvents(?DateTimeImmutable $time = null, bool $maintenanceMode = false): array
    {
        $due = [];

        foreach ($this->events as $event) {
            if ($maintenanceMode && !$event->runsInMaintenanceMode()) {
                continue;
            }

            if ($event->isDue($time)) {
                $due[] = $event->name();
            }
        }

        return $due;
    }

    /**
     * @return list<string>
     */
    public function runDue(?DateTimeImmutable $time = null, bool $maintenanceMode = false): array
    {
        $ran = [];

        foreach ($this->events as $event) {
            if ($maintenanceMode && !$event->runsInMaintenanceMode()) {
                continue;
            }

            if (!$event->isDue($time)) {
                continue;
            }

            if ($event->preventsOverlapping() && !$this->mutex->acquire($event->name())) {
                continue;
            }

            try {
                $event->run();
                $ran[] = $event->name() . ($event->runsInBackground() ? ' (background)' : '');
            } finally {
                if ($event->preventsOverlapping()) {
                    $this->mutex->release($event->name());
                }
            }
        }

        return $ran;
    }
}
