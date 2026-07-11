<?php

declare(strict_types=1);

namespace WTD\Security;

use WTD\Session\SessionStore;

final class CsrfTokenManager
{
    public function __construct(private readonly SessionStore $session, private readonly string $key = '_csrf_token')
    {
    }

    public function token(): string
    {
        $token = $this->session->get($this->key);

        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $this->session->put($this->key, $token);
        }

        return $token;
    }

    public function validate(?string $token): bool
    {
        $known = $this->session->get($this->key);

        return is_string($known) && is_string($token) && hash_equals($known, $token);
    }
}
