<?php

declare(strict_types=1);

namespace WTD\Auth;

final class GenericUser implements Authenticatable
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(private array $attributes)
    {
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->attributes['id'] ?? null;
    }

    public function getAuthPassword(): string
    {
        return (string) ($this->attributes['password'] ?? '');
    }

    public function getRememberToken(): ?string
    {
        $token = $this->attributes['remember_token'] ?? null;

        return is_string($token) ? $token : null;
    }

    public function setRememberToken(?string $token): void
    {
        $this->attributes['remember_token'] = $token;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
