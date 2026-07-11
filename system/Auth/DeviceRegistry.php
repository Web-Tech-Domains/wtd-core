<?php

declare(strict_types=1);

namespace WTD\Auth;

final class DeviceRegistry
{
    /**
     * @var array<string, list<array<string, mixed>>>
     */
    private array $devices = [];

    public function remember(Authenticatable $user, string $name): string
    {
        $id = bin2hex(random_bytes(16));
        $this->devices[(string) $user->getAuthIdentifier()][] = [
            'id' => $id,
            'name' => $name,
            'last_used_at' => time(),
        ];

        return $id;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function devices(Authenticatable $user): array
    {
        return $this->devices[(string) $user->getAuthIdentifier()] ?? [];
    }

    public function forget(Authenticatable $user, string $id): void
    {
        $key = (string) $user->getAuthIdentifier();
        $this->devices[$key] = array_values(array_filter(
            $this->devices[$key] ?? [],
            static fn (array $device): bool => ($device['id'] ?? null) !== $id,
        ));
    }
}
