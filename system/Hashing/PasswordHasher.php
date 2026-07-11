<?php

declare(strict_types=1);

namespace WTD\Hashing;

final class PasswordHasher
{
    public function make(string $value): string
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    public function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
