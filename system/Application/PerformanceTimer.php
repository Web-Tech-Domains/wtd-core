<?php

declare(strict_types=1);

namespace WTD\Application;

/**
 * Measures elapsed wall time for framework operations.
 */
final class PerformanceTimer
{
    private float $startedAt;

    public function __construct()
    {
        $this->restart();
    }

    /**
     * Restart the timer from the current instant.
     */
    public function restart(): void
    {
        $this->startedAt = microtime(true);
    }

    /**
     * Return elapsed milliseconds since the timer started.
     */
    public function elapsedMilliseconds(): float
    {
        return (microtime(true) - $this->startedAt) * 1000;
    }
}
