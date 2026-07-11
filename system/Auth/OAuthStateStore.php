<?php

declare(strict_types=1);

namespace WTD\Auth;

final class OAuthStateStore
{
    /**
     * @var array<string, int>
     */
    private array $states = [];

    public function create(int $ttlSeconds = 600): string
    {
        $state = bin2hex(random_bytes(20));
        $this->states[$state] = time() + $ttlSeconds;

        return $state;
    }

    public function consume(string $state): bool
    {
        $expires = $this->states[$state] ?? null;
        unset($this->states[$state]);

        return is_int($expires) && $expires >= time();
    }
}
