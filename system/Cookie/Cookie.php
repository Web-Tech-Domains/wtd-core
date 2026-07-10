<?php

declare(strict_types=1);

namespace WTD\Cookie;

/**
 * Represents an HTTP cookie header value.
 */
final class Cookie
{
    public function __construct(
        private readonly string $name,
        private readonly string $value,
        private readonly int $expires = 0,
        private readonly string $path = '/',
        private readonly ?string $domain = null,
        private readonly bool $secure = false,
        private readonly bool $httpOnly = true,
        private readonly string $sameSite = 'Lax',
    ) {
    }

    /**
     * Return the cookie name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Return the cookie value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Render a Set-Cookie header value.
     */
    public function toHeader(): string
    {
        $parts = [
            rawurlencode($this->name) . '=' . rawurlencode($this->value),
            'Path=' . $this->path,
            'SameSite=' . $this->sameSite,
        ];

        if ($this->expires > 0) {
            $parts[] = 'Expires=' . gmdate('D, d M Y H:i:s T', $this->expires);
        }

        if ($this->domain !== null) {
            $parts[] = 'Domain=' . $this->domain;
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        return implode('; ', $parts);
    }
}
