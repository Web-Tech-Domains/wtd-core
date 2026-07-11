<?php

declare(strict_types=1);

namespace WTD\Authorization;

final class RbacManager
{
    /**
     * @var array<string, list<string>>
     */
    private array $roles = [];

    /**
     * @var array<string, list<string>>
     */
    private array $rolePermissions = [];

    public function assignRole(mixed $userId, string $role): void
    {
        $key = (string) $userId;
        $this->roles[$key] ??= [];

        if (!in_array($role, $this->roles[$key], true)) {
            $this->roles[$key][] = $role;
        }
    }

    public function givePermissionToRole(string $role, string $permission): void
    {
        $this->rolePermissions[$role] ??= [];

        if (!in_array($permission, $this->rolePermissions[$role], true)) {
            $this->rolePermissions[$role][] = $permission;
        }
    }

    public function hasRole(mixed $userId, string $role): bool
    {
        return in_array($role, $this->roles[(string) $userId] ?? [], true);
    }

    public function can(mixed $userId, string $permission): bool
    {
        foreach ($this->roles[(string) $userId] ?? [] as $role) {
            if (in_array($permission, $this->rolePermissions[$role] ?? [], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    public function roles(mixed $userId): array
    {
        return $this->roles[(string) $userId] ?? [];
    }
}
