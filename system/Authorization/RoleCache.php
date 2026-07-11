<?php

declare(strict_types=1);

namespace WTD\Authorization;

final class RoleCache
{
    /**
     * @var array<string, list<string>>
     */
    private array $roles = [];

    /**
     * @param list<string> $roles
     */
    public function put(mixed $userId, array $roles): void
    {
        $this->roles[(string) $userId] = array_values($roles);
    }

    /**
     * @return list<string>|null
     */
    public function get(mixed $userId): ?array
    {
        return $this->roles[(string) $userId] ?? null;
    }

    public function forget(mixed $userId): void
    {
        unset($this->roles[(string) $userId]);
    }

    public function flush(): void
    {
        $this->roles = [];
    }
}
