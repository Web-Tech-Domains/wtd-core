<?php

declare(strict_types=1);

namespace WTD\DeveloperExperience;

use Closure;

/**
 * Captures lightweight runtime timing and memory information.
 */
final class Profiler
{
    private readonly float $startedAt;

    /**
     * @var list<array{name: string, elapsed_ms: float}>
     */
    private array $marks = [];

    public function __construct()
    {
        $this->startedAt = microtime(true);
    }

    public function mark(string $name): void
    {
        $this->marks[] = [
            'name' => $name,
            'elapsed_ms' => $this->elapsedMilliseconds(),
        ];
    }

    /**
     * @template T
     *
     * @param Closure(): T $callback
     *
     * @return array{result: T, elapsed_ms: float}
     */
    public function measure(Closure $callback): array
    {
        $start = microtime(true);
        $result = $callback();

        return [
            'result' => $result,
            'elapsed_ms' => (microtime(true) - $start) * 1000,
        ];
    }

    /**
     * @return array{elapsed_ms: float, memory_usage: int, memory_peak: int, marks: list<array{name: string, elapsed_ms: float}>}
     */
    public function snapshot(): array
    {
        return [
            'elapsed_ms' => $this->elapsedMilliseconds(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'marks' => $this->marks,
        ];
    }

    private function elapsedMilliseconds(): float
    {
        return (microtime(true) - $this->startedAt) * 1000;
    }
}
