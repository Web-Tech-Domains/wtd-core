<?php

declare(strict_types=1);

namespace WTD\Auth;

use WTD\Hashing\PasswordHasher;

final class ArrayUserProvider implements UserProvider
{
    /**
     * @param list<array<string, mixed>> $users
     */
    public function __construct(
        private array $users,
        private readonly PasswordHasher $hasher = new PasswordHasher(),
        private readonly string $identifier = 'email',
    ) {
    }

    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        return $this->firstWhere('id', $identifier);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (!array_key_exists($this->identifier, $credentials)) {
            return null;
        }

        return $this->firstWhere($this->identifier, $credentials[$this->identifier]);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $password = $credentials['password'] ?? null;

        return is_string($password) && $this->hasher->verify($password, $user->getAuthPassword());
    }

    public function retrieveByToken(string $token): ?Authenticatable
    {
        return $this->firstWhere('api_token', hash('sha256', $token));
    }

    public function updateRememberToken(Authenticatable $user, string $token): void
    {
        foreach ($this->users as &$stored) {
            if (($stored['id'] ?? null) === $user->getAuthIdentifier()) {
                $stored['remember_token'] = $token;
                $user->setRememberToken($token);
                return;
            }
        }
    }

    private function firstWhere(string $key, mixed $value): ?Authenticatable
    {
        foreach ($this->users as $user) {
            if (($user[$key] ?? null) === $value) {
                return new GenericUser($user);
            }
        }

        return null;
    }
}
