<?php

declare(strict_types=1);

namespace WTD\Scheduler;

use Closure;
use DateTimeImmutable;

final class Event
{
    private string $expression = '* * * * *';

    private string $timezone = 'UTC';

    private bool $withoutOverlapping = false;

    private bool $runInBackground = false;

    private bool $evenInMaintenanceMode = false;

    public function __construct(
        private readonly string $name,
        private readonly Closure $callback,
    ) {
    }

    public function cron(string $expression): self
    {
        $this->expression = $expression;

        return $this;
    }

    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    public function dailyAt(string $time): self
    {
        [$hour, $minute] = array_map('intval', explode(':', $time, 2));

        return $this->cron(sprintf('%d %d * * *', $minute, $hour));
    }

    public function timezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function withoutOverlapping(): self
    {
        $this->withoutOverlapping = true;

        return $this;
    }

    public function runInBackground(): self
    {
        $this->runInBackground = true;

        return $this;
    }

    public function evenInMaintenanceMode(): self
    {
        $this->evenInMaintenanceMode = true;

        return $this;
    }

    public function isDue(?DateTimeImmutable $time = null): bool
    {
        return (new CronExpression($this->expression))->isDue($time, $this->timezone);
    }

    public function run(): void
    {
        ($this->callback)();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function preventsOverlapping(): bool
    {
        return $this->withoutOverlapping;
    }

    public function runsInBackground(): bool
    {
        return $this->runInBackground;
    }

    public function runsInMaintenanceMode(): bool
    {
        return $this->evenInMaintenanceMode;
    }
}
