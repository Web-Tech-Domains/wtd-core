<?php

declare(strict_types=1);

namespace WTD\Security;

final class RateLimiter
{
    /**
     * @var array<string, array{count: int, reset: int}>
     */
    private array $hits = [];

    public function hit(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $now = time();
        $record = $this->hits[$key] ?? ['count' => 0, 'reset' => $now + $decaySeconds];

        if ($record['reset'] <= $now) {
            $record = ['count' => 0, 'reset' => $now + $decaySeconds];
        }

        $record['count']++;
        $this->hits[$key] = $record;

        return $record['count'] <= $maxAttempts;
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - ($this->hits[$key]['count'] ?? 0));
    }
}
