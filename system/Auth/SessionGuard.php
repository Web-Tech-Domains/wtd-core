<?php

declare(strict_types=1);

namespace WTD\Auth;

use WTD\Session\SessionStore;

final class SessionGuard
{
    private ?Authenticatable $user = null;

    public function __construct(
        private readonly UserProvider $provider,
        private readonly SessionStore $session,
        private readonly string $sessionKey = 'auth_user_id',
    ) {
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $identifier = $this->session->get($this->sessionKey);
        $this->user = $identifier === null ? null : $this->provider->retrieveById($identifier);

        return $this->user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user === null || !$this->provider->validateCredentials($user, $credentials)) {
            return false;
        }

        $this->login($user, $remember);

        return true;
    }

    public function login(Authenticatable $user, bool $remember = false): void
    {
        $this->user = $user;
        $this->session->put($this->sessionKey, $user->getAuthIdentifier());

        if ($remember && $this->provider instanceof ArrayUserProvider) {
            $this->provider->updateRememberToken($user, bin2hex(random_bytes(30)));
        }
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->forget($this->sessionKey);
    }
}
