<?php

declare(strict_types=1);

namespace WTD\Cookie;

use InvalidArgumentException;

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
        $this->guardValidName($name);
        $this->guardHeaderSafe('value', $value);
        $this->guardHeaderSafe('path', $path);

        if ($domain !== null) {
            $this->guardHeaderSafe('domain', $domain);
        }

        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException('Cookie SameSite must be Lax, Strict, or None.');
        }

        if ($sameSite === 'None' && !$secure) {
            throw new InvalidArgumentException('Cookies with SameSite=None must be secure.');
        }
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

    private function guardValidName(string $name): void
    {
        if ($name === '' || preg_match('/[=,; \t\r\n\x0b\x0c]/', $name) === 1) {
            throw new InvalidArgumentException('Cookie name contains invalid characters.');
        }
    }

    private function guardHeaderSafe(string $field, string $value): void
    {
        if (str_contains($value, "\r") || str_contains($value, "\n")) {
            throw new InvalidArgumentException(sprintf('Cookie %s contains unsafe header characters.', $field));
        }
    }
}
