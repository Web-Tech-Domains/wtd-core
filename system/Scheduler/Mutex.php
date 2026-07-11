<?php

declare(strict_types=1);

namespace WTD\Scheduler;

final class Mutex
{
    /**
     * @var array<string, bool>
     */
    private array $locks = [];

    public function acquire(string $name): bool
    {
        if (($this->locks[$name] ?? false) === true) {
            return false;
        }

        $this->locks[$name] = true;

        return true;
    }

    public function release(string $name): void
    {
        unset($this->locks[$name]);
    }

    public function locked(string $name): bool
    {
        return $this->locks[$name] ?? false;
    }
}
