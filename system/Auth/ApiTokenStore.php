<?php

declare(strict_types=1);

namespace WTD\Auth;

final class ApiTokenStore
{
    /**
     * @var array<string, Authenticatable>
     */
    private array $tokens = [];

    public function issue(Authenticatable $user): string
    {
        $plain = bin2hex(random_bytes(32));
        $this->tokens[hash('sha256', $plain)] = $user;

        return $plain;
    }

    public function userForToken(string $token): ?Authenticatable
    {
        return $this->tokens[hash('sha256', $token)] ?? null;
    }

    public function revoke(string $token): void
    {
        unset($this->tokens[hash('sha256', $token)]);
    }
}
