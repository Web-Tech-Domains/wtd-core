<?php

declare(strict_types=1);

namespace WTD\Auth;

interface UserProvider
{
    public function retrieveById(mixed $identifier): ?Authenticatable;

    /**
     * @param array<string, mixed> $credentials
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable;

    /**
     * @param array<string, mixed> $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool;

    public function retrieveByToken(string $token): ?Authenticatable;
}
