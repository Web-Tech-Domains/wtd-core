<?php

declare(strict_types=1);

namespace WTD\Monitoring;

final class MetricsRegistry
{
    /**
     * @var array<string, float|int>
     */
    private array $metrics = [];

    public function set(string $name, float|int $value): void
    {
        $this->metrics[$name] = $value;
    }

    public function increment(string $name, int $by = 1): void
    {
        $this->metrics[$name] = ($this->metrics[$name] ?? 0) + $by;
    }

    /**
     * @return array<string, float|int>
     */
    public function all(): array
    {
        ksort($this->metrics);

        return $this->metrics;
    }
}
