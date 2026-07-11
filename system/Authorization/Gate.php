<?php

declare(strict_types=1);

namespace WTD\Authorization;

use WTD\Auth\Authenticatable;

final class Gate
{
    /**
     * @var array<string, callable(Authenticatable|null, mixed ...): bool>
     */
    private array $abilities = [];

    /**
     * @param callable(Authenticatable|null, mixed ...): bool $callback
     */
    public function define(string $ability, callable $callback): void
    {
        $this->abilities[$ability] = $callback;
    }

    public function allows(?Authenticatable $user, string $ability, mixed ...$arguments): bool
    {
        $callback = $this->abilities[$ability] ?? null;

        return $callback !== null && (bool) $callback($user, ...$arguments);
    }

    public function denies(?Authenticatable $user, string $ability, mixed ...$arguments): bool
    {
        return !$this->allows($user, $ability, ...$arguments);
    }
}
