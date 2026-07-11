<?php

declare(strict_types=1);

namespace WTD\Auth;

final class TokenBroker
{
    /**
     * @var array<string, array{identifier: mixed, expires: int}>
     */
    private array $tokens = [];

    public function create(Authenticatable $user, int $ttlSeconds = 3600): string
    {
        $token = bin2hex(random_bytes(32));
        $this->tokens[hash('sha256', $token)] = [
            'identifier' => $user->getAuthIdentifier(),
            'expires' => time() + $ttlSeconds,
        ];

        return $token;
    }

    public function validate(Authenticatable $user, string $token): bool
    {
        $record = $this->tokens[hash('sha256', $token)] ?? null;

        return $record !== null
            && $record['identifier'] === $user->getAuthIdentifier()
            && $record['expires'] >= time();
    }

    public function consume(Authenticatable $user, string $token): bool
    {
        if (!$this->validate($user, $token)) {
            return false;
        }

        unset($this->tokens[hash('sha256', $token)]);

        return true;
    }
}
