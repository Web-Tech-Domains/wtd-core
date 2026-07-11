<?php

declare(strict_types=1);

namespace WTD\Auth;

final class JwtService
{
    public function __construct(private readonly string $secret)
    {
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function issue(Authenticatable $user, int $ttlSeconds = 3600, array $claims = []): string
    {
        return $this->encode(array_merge($claims, [
            'sub' => $user->getAuthIdentifier(),
            'exp' => time() + $ttlSeconds,
        ]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function validate(string $jwt): ?array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->sign($header . '.' . $payload);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $claims = json_decode((string) $this->base64UrlDecode($payload), true);

        if (!is_array($claims)) {
            return null;
        }

        $expires = $claims['exp'] ?? null;

        if (is_int($expires) && $expires < time()) {
            return null;
        }

        /** @var array<string, mixed> $claims */
        return $claims;
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function encode(array $claims): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));

        return $header . '.' . $payload . '.' . $this->sign($header . '.' . $payload);
    }

    private function sign(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->secret, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string|false
    {
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
}
