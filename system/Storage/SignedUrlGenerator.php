<?php

declare(strict_types=1);

namespace WTD\Storage;

final class SignedUrlGenerator
{
    public function __construct(private readonly string $secret)
    {
    }

    public function sign(string $url, int $expiresAt): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        $unsigned = $url . $separator . 'expires=' . $expiresAt;

        return $unsigned . '&signature=' . hash_hmac('sha256', $unsigned, $this->secret);
    }

    public function validate(string $url): bool
    {
        $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);
        $signature = $query['signature'] ?? null;
        $expires = $query['expires'] ?? null;

        if (!is_string($signature) || !is_numeric($expires) || (int) $expires < time()) {
            return false;
        }

        unset($query['signature']);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = (string) ($parts['host'] ?? '');
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $base = $scheme . $host . $port . ($parts['path'] ?? '') . '?' . http_build_query($query);

        return hash_equals(hash_hmac('sha256', $base, $this->secret), $signature);
    }
}
