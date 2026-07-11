<?php

declare(strict_types=1);

namespace WTD\Security;

final class SignedUrl
{
    public function __construct(private readonly string $secret)
    {
    }

    public function sign(string $url, ?int $expiresAt = null): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        $unsigned = $expiresAt === null ? $url : $url . $separator . 'expires=' . $expiresAt;
        $separator = str_contains($unsigned, '?') ? '&' : '?';

        return $unsigned . $separator . 'signature=' . hash_hmac('sha256', $unsigned, $this->secret);
    }

    public function validate(string $url): bool
    {
        $parts = parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);
        $signature = $query['signature'] ?? null;

        if (!is_string($signature)) {
            return false;
        }

        if (isset($query['expires']) && (int) $query['expires'] < time()) {
            return false;
        }

        unset($query['signature']);
        $base = ($parts['path'] ?? '') . ($query === [] ? '' : '?' . http_build_query($query));

        return hash_equals(hash_hmac('sha256', $base, $this->secret), $signature);
    }
}
